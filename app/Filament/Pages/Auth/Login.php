<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Enums\AuthMode;
use App\Filament\Pages\Auth\Concerns\UsesConfiguredAuthLayout;
use App\Models\User;
use App\Services\Auth\LdapAuthService;
use App\Services\Auth\LdapUserService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\MultiFactor\Contracts\HasBeforeChallengeHook;
use Filament\Auth\Pages\Login as VendorLogin;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class Login extends VendorLogin
{
    use UsesConfiguredAuthLayout;

    private AuthMode $authMode;

    private ?LdapAuthService $ldapAuthService = null;

    private ?LdapUserService $ldapUserService = null;

    /**
     * Create a new login page instance.
     */
    public function __construct()
    {
        $this->authMode = AuthMode::fromConfig(Config::string('auth.mode'));

        if ($this->authMode === AuthMode::Ldap) {
            $this->ldapAuthService = app(LdapAuthService::class);
            $this->ldapUserService = app(LdapUserService::class);
        }
    }

    /**
     * Get the login view path.
     */
    public function getView(): string
    {
        return 'filament.pages.auth.login';
    }

    /**
     * Authenticate the current user with the configured auth mode.
     */
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        /** @var array<string, string> $data */
        $data = $this->form->getState();

        match ($this->authMode) {
            AuthMode::Ldap  => $this->attemptLdapAuth($data),
            AuthMode::Local => $this->attemptLocalAuth($data),
        };

        session()->regenerate();

        return app(LoginResponse::class);
    }

    /**
     * @param  array<string, string>  $data
     */
    private function attemptLocalAuth(array $data): void
    {
        /** @var SessionGuard $authGuard */
        $authGuard = Filament::auth();

        $authProvider = $authGuard->getProvider();
        $credentials = $this->getCredentialsFromFormData($data);
        $user = $authProvider->retrieveByCredentials($credentials);

        if ((! $user) || (! $authProvider->validateCredentials($user, $credentials))) {
            $this->userUndertakingMultiFactorAuthentication = null;

            $this->fireFailedEvent($authGuard, $user, $credentials);
            $this->throwFailureValidationException();
        }

        $this->checkMultiFactorAuthentication($user);

        $this->attemptDefaultAuth(
            $authGuard,
            $user,
            $credentials,
            (bool) ($data['remember'] ?? false),
        );
    }

    /**
     * Validate if multi-factor authentication is required for the user.
     */
    public function checkMultiFactorAuthentication(Authenticatable $user): void
    {
        if (
            filled($this->userUndertakingMultiFactorAuthentication) &&
            (decrypt($this->userUndertakingMultiFactorAuthentication) === $user->getAuthIdentifier())
        ) {
            $this->multiFactorChallengeForm->validate();
        } else {
            foreach (Filament::getMultiFactorAuthenticationProviders() as $multiFactorAuthenticationProvider) {
                if (! $multiFactorAuthenticationProvider->isEnabled($user)) {
                    continue;
                }

                $this->userUndertakingMultiFactorAuthentication = encrypt($user->getAuthIdentifier());

                if ($multiFactorAuthenticationProvider instanceof HasBeforeChallengeHook) {
                    $multiFactorAuthenticationProvider->beforeChallenge($user);
                }

                break;
            }

            if (filled($this->userUndertakingMultiFactorAuthentication)) {
                $this->multiFactorChallengeForm->fill();

                return;
            }
        }
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    public function attemptDefaultAuth(SessionGuard $authGuard, Authenticatable $user, array $credentials, bool $remember): void
    {
        if (! $authGuard->attemptWhen($credentials, function (Authenticatable $user): bool {
            if (! ($user instanceof FilamentUser)) {
                return true;
            }

            /** @var Panel $panel */
            $panel = Filament::getCurrentOrDefaultPanel();

            return $user->canAccessPanel($panel);
        }, $remember)) {
            $this->fireFailedEvent($authGuard, $user, $credentials);
            $this->throwFailureValidationException();
        }
    }

    /**
     * @param  array<string, string>  $data
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function attemptLdapAuth(array $data): void
    {
        $username = $this->normalizeUsername($data['username'] ?? null);
        $password = $data['password'] ?? '';

        $ldapUser = $this->getLdapUserService()->findUserByUsername($username);

        if (! $ldapUser) {
            $this->throwFailureValidationException();
        }

        $validLogin = $this->getLdapAuthService()->authenticate($username, $password);

        if (! $validLogin) {
            $this->throwFailureValidationException();
        }

        Filament::auth()->login(
            user: $this->handleLocalUserRecord($username, $ldapUser),
            remember: (bool) ($data['remember'] ?? false),
        );
    }

    /**
     * Build the login form schema according to auth mode.
     */
    public function form(Schema $schema): Schema
    {
        $loginComponent = $this->authMode->usesUsernameField()
            ? $this->getUsernameFormComponent()
            : $this->getEmailFormComponent();

        return $schema
            ->components([
                $loginComponent,
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    /**
     * Get the username input component used by LDAP mode.
     */
    protected function getUsernameFormComponent(): Component
    {
        $emailDomain = $this->getLdapAuthService()->emailDomain;

        return TextInput::make('username')
            ->label(__('filament-panels::pages/auth/login.form.username.label'))
            ->required()
            ->autocomplete()
            ->suffix($emailDomain)
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1])
        ;
    }

    /**
     * @param  LdapUser  $ldapUser
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function handleLocalUserRecord(string $username, $ldapUser): User
    {
        if (Config::boolean('auth.ldap.requires_local')) {
            $user = User::firstWhere('username', $username);

            if (! $user || $user->isInactive()) {
                $this->throwFailureValidationException();
            }

            return $user;
        }

        $user = User::firstOrCreate(
            ['username' => $username],
            [
                'name'      => $this->getLdapUserService()->getUserInfo('displayName', $ldapUser),
                'email'     => $this->getLdapUserService()->getUserInfo('mail', $ldapUser),
                'password'  => Hash::make(\Str::random(16)),
                'is_active' => true,
            ],
        );

        if ($user->roles->isEmpty()) {
            $user->syncRoles([Config::string('auth.default_role')]);
        }

        return $user;
    }

    /**
     * Normalize the username before LDAP authentication.
     */
    protected function normalizeUsername(?string $username): string
    {
        if (is_null($username)) {
            return '';
        }

        $str = str($username)
            ->lower()
            ->trim()
        ;

        $emailDomain = config()->string('auth.ldap.email_domain');

        if ($str->contains($emailDomain)) {
            $str = $str->before($emailDomain);
        }

        return $str->toString();
    }

    /**
     * Resolve LDAP auth service from memory or container.
     */
    private function getLdapAuthService(): LdapAuthService
    {
        return $this->ldapAuthService ?? app(LdapAuthService::class);
    }

    /**
     * Resolve LDAP user service from memory or container.
     */
    private function getLdapUserService(): LdapUserService
    {
        return $this->ldapUserService ?? app(LdapUserService::class);
    }
}

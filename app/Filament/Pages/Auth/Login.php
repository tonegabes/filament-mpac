<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Services\Auth\LdapAuthService;
use App\Services\Auth\LdapUserService;
use App\Settings\SystemSettings;
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
    private LdapAuthService $ldapAuthService;

    private LdapUserService $ldapUserService;

    private bool $isLdapEnabled;

    public function __construct()
    {
        $this->isLdapEnabled = Config::boolean('auth.ldap.enabled', false);

        if ($this->isLdapEnabled) {
            $this->ldapAuthService = new LdapAuthService;
            $this->ldapUserService = new LdapUserService;
        }
    }

    public function getView(): string
    {
        return 'filament.pages.auth.login';
    }

    public function getLayout(): string
    {
        return app(SystemSettings::class)->getAppLayout();
    }

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

        if ($this->isLdapEnabled) {
            $this->attemptLdapAuth($data);
        } else {

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

        session()->regenerate();

        return app(LoginResponse::class);
    }

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
        $username = $this->normalizeUsername($data['username']);
        $password = $data['password'] ?? '';

        // Check if user is in LDAP
        $ldapUser = $this->ldapUserService->findUserByUsername($username);

        if (! $ldapUser) {
            $this->throwFailureValidationException();
        }

        // Validate the LDAP user is active
        $validLogin = $this->ldapAuthService->authenticate($username, $password);

        if (! $validLogin) {
            $this->throwFailureValidationException();
        }

        // Login the user
        Filament::auth()->login(
            user: $this->handleLocalUserRecord($username, $ldapUser),
            remember: (bool) ($data['remember'] ?? false),
        );
    }

    public function form(Schema $schema): Schema
    {
        $loginComponent = $this->getEmailFormComponent();

        if ($this->isLdapEnabled) {
            $loginComponent = $this->getUsernameFormComponent();
        }

        return $schema
            ->components([
                $loginComponent,
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    protected function getUsernameFormComponent(): Component
    {
        $emailDomain = $this->ldapAuthService->emailDomain;

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
                'name'      => $this->ldapUserService->getUserInfo('displayName', $ldapUser),
                'email'     => $this->ldapUserService->getUserInfo('mail', $ldapUser),
                'password'  => Hash::make(\Str::random(16)),
                'is_active' => true,
            ],
        );

        if ($user->roles->isEmpty()) {
            $user->syncRoles([Config::string('auth.default_role')]);
        }

        return $user;
    }

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
}

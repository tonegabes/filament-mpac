<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Enums\AuthMode;
use App\Exceptions\DirectoryUnavailableException;
use App\Filament\Pages\Auth\Concerns\UsesConfiguredAuthLayout;
use App\Models\User;
use App\Services\Auth\AuthModeHandler;
use App\Services\Auth\AuthModeHandlerResolver;
use App\Services\Auth\LdapAuthService;
use App\Services\Auth\LdapUserService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as VendorLogin;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Support\Timebox;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class Login extends VendorLogin
{
    use UsesConfiguredAuthLayout;

    private AuthModeHandler $authModeHandler;

    private ?LdapAuthService $ldapAuthService = null;

    private ?LdapUserService $ldapUserService = null;

    /**
     * Resolve auth mode dependencies on Livewire boot.
     */
    public function boot(): void
    {
        $authMode = AuthMode::fromConfig(Config::string('auth.mode'));
        $this->authModeHandler = app(AuthModeHandlerResolver::class)->resolve($authMode);

        if ($authMode === AuthMode::Ldap) {
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
        if ($this->authModeHandler->mode() === AuthMode::Local) {
            return parent::authenticate();
        }

        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        /** @var array<string, string> $data */
        $data = $this->form->getState();

        try {
            $this->attemptLdapAuth($data);
        } catch (DirectoryUnavailableException $exception) {
            Notification::make()
                ->danger()
                ->title('Erro')
                ->body($exception->getMessage())
                ->send();

            return null;
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    /**
     * @param  array<string, string>  $data
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws DirectoryUnavailableException
     */
    public function attemptLdapAuth(array $data): void
    {
        $username = $this->normalizeUsername($data['username'] ?? null);
        $password = $data['password'] ?? '';
        $remember = (bool) ($data['remember'] ?? false);

        /** @var SessionGuard $authGuard */
        $authGuard = Filament::auth();
        $credentials = ['username' => $username, 'password' => $password];
        $timeboxDuration = (int) config('auth.timebox_duration', 200_000);

        $user = app(Timebox::class)->call(function (Timebox $timebox) use ($authGuard, $credentials, $username, $password, $remember): User {
            $this->fireAttemptingEvent($authGuard, $credentials, $remember);

            if ($username === '' || $password === '') {
                $this->fireFailedEvent($authGuard, null, $credentials);
                $this->throwFailureValidationException();
            }

            $ldapUser = $this->getLdapUserService()->findUserByUsername($username);

            if (! $ldapUser) {
                $this->fireFailedEvent($authGuard, null, $credentials);
                $this->throwFailureValidationException();
            }

            $validLogin = $this->getLdapAuthService()->authenticate($username, $password);

            if (! $validLogin) {
                $this->fireFailedEvent($authGuard, null, $credentials);
                $this->throwFailureValidationException();
            }

            $user = $this->handleLocalUserRecord($username, $ldapUser);

            if (! $this->isUserAllowedToAccessPanel($user)) {
                $this->fireFailedEvent($authGuard, $user, $credentials);
                $this->throwFailureValidationException();
            }

            $timebox->returnEarly();

            return $user;
        }, $timeboxDuration);

        $authGuard->login(
            user: $user,
            remember: $remember,
        );
    }

    /**
     * Build the login form schema according to auth mode.
     */
    public function form(Schema $schema): Schema
    {
        $loginComponent = $this->authModeHandler->loginComponent($this);

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
        ;
    }

    protected function getPasswordFormComponent(): Component
    {
        /** @var TextInput $password */
        $password = parent::getPasswordFormComponent();

        return $password->prefixIcon(Phosphor::Lock);
    }

    protected function getRememberFormComponent(): Component
    {
        /** @var Checkbox $remember */
        $remember = parent::getRememberFormComponent();

        return $remember->label('Lembrar meu acesso');
    }

    protected function getAuthenticateFormAction(): Action
    {
        $submitAction = parent::getAuthenticateFormAction();

        return $submitAction->label('Entrar');
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    private function handleLocalUserRecord(string $username, LdapUser $ldapUser): User
    {
        if (Config::boolean('auth.ldap.requires_local')) {
            $user = User::firstWhere('username', $username);

            if (! $user || $user->is_active === false) {
                $this->throwFailureValidationException();
            }

            return $user;
        }

        $user = User::firstOrCreate(
            ['username' => $username],
            [
                'name' => $this->getLdapUserService()->getUserInfo('displayName', $ldapUser),
                'email' => $this->getLdapUserService()->getUserInfo('mail', $ldapUser),
                'password' => Str::random(16),
                'is_active' => true,
            ],
        );

        if (! $user->is_active) {
            $this->throwFailureValidationException();
        }

        if ($user->roles()->doesntExist()) {
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

    /**
     * Build the default email component used by local authentication.
     */
    public function makeEmailLoginFormComponent(): Component
    {
        return $this->getEmailFormComponent();
    }

    /**
     * Build the username component used by LDAP authentication.
     */
    public function makeUsernameLoginFormComponent(): Component
    {
        return $this->getUsernameFormComponent();
    }
}

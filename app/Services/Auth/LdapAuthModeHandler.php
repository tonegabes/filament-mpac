<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Enums\AuthMode;
use App\Filament\Pages\Auth\Login;
use Filament\Schemas\Components\Component;

final class LdapAuthModeHandler implements AuthModeHandler
{
    public function mode(): AuthMode
    {
        return AuthMode::Ldap;
    }

    public function allowsLocalRegistration(): bool
    {
        return false;
    }

    public function loginComponent(Login $loginPage): Component
    {
        return $loginPage->makeUsernameLoginFormComponent();
    }

    /**
     * @param  array<string, string>  $data
     */
    public function authenticate(Login $loginPage, array $data): void
    {
        $loginPage->attemptLdapAuth($data);
    }
}

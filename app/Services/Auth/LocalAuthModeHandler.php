<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Enums\AuthMode;
use App\Filament\Pages\Auth\Login;
use Filament\Schemas\Components\Component;

final class LocalAuthModeHandler implements AuthModeHandler
{
    public function mode(): AuthMode
    {
        return AuthMode::Local;
    }

    public function allowsLocalRegistration(): bool
    {
        return true;
    }

    public function loginComponent(Login $loginPage): Component
    {
        return $loginPage->makeEmailLoginFormComponent();
    }

    /**
     * @param  array<string, string>  $data
     */
    public function authenticate(Login $loginPage, array $data): void
    {
        $loginPage->attemptLocalAuth($data);
    }
}

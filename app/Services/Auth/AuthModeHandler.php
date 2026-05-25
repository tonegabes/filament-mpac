<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Enums\AuthMode;
use App\Filament\Pages\Auth\Login;
use Filament\Schemas\Components\Component;

interface AuthModeHandler
{
    public function mode(): AuthMode;

    public function allowsLocalRegistration(): bool;

    public function loginComponent(Login $loginPage): Component;

    /**
     * @param  array<string, string>  $data
     */
    public function authenticate(Login $loginPage, array $data): void;
}

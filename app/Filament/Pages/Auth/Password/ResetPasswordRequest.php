<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth\Password;

use App\Filament\Pages\Auth\Concerns\UsesConfiguredAuthLayout;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;

class ResetPasswordRequest extends RequestPasswordReset
{
    use UsesConfiguredAuthLayout;

    protected string $view = 'filament.pages.auth.password.reset-password-request';
}

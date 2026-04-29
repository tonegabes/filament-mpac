<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth\Password;

use App\Filament\Pages\Auth\Concerns\UsesConfiguredAuthLayout;
use Filament\Auth\Pages\PasswordReset\ResetPassword;

class ResetPasswordAction extends ResetPassword
{
    use UsesConfiguredAuthLayout;

    protected string $view = 'filament.pages.auth.password.reset-password-action';
}

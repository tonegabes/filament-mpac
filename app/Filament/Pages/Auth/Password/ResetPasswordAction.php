<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth\Password;

use Filament\Auth\Pages\PasswordReset\ResetPassword;

class ResetPasswordAction extends ResetPassword
{
    protected static string $layout = 'layouts.auth';

    protected string $view = 'filament.pages.auth.password.reset-password-action';
}

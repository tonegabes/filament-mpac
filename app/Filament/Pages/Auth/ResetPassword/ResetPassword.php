<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth\ResetPassword;

use Filament\Auth\Pages\PasswordReset\ResetPassword as VendorResetPassword;

class ResetPassword extends VendorResetPassword
{
    protected static string $layout = 'layouts.auth';

    protected string $view = 'filament.pages.auth.password.reset-password';
}

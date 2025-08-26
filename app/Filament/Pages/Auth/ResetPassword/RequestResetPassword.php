<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth\ResetPassword;

use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as VendorRequestPasswordReset;

class RequestResetPassword extends VendorRequestPasswordReset
{
    protected static string $layout = 'layouts.auth';

    protected string $view = 'filament.pages.auth.password.request-reset-password';
}

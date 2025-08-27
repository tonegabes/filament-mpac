<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth\Password;

use App\Settings\SystemSettings;
use Filament\Auth\Pages\PasswordReset\ResetPassword;

class ResetPasswordAction extends ResetPassword
{
    protected string $view = 'filament.pages.auth.password.reset-password-action';

    /**
     * Override the default layout to use the custom layout.
     */
    public function getLayout(): string
    {
        return app(SystemSettings::class)->getAppLayout();
    }
}

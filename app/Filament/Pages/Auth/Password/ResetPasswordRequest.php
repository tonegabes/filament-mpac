<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth\Password;

use App\Settings\SystemSettings;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;

class ResetPasswordRequest extends RequestPasswordReset
{
    protected string $view = 'filament.pages.auth.password.reset-password-request';

    /**
     * Override the default layout to use the custom layout.
     */
    public function getLayout(): string
    {
        return app(SystemSettings::class)->getAppLayout();
    }
}

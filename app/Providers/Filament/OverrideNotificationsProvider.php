<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Notifications\ResetPasswordNotification;
use Filament\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class OverrideNotificationsProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Override the default reset password notification
        $this->app->bind(ResetPassword::class, ResetPasswordNotification::class);
    }
}

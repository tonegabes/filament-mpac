<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Mpac\FilamentBranding\Enums\SystemPermissions;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePulse();
    }

    /**
     * Configura o Laravel Pulse.
     */
    private function configurePulse(): void
    {
        Gate::define('viewPulse', function (User $user): bool {
            return $user->can(SystemPermissions::PulseAccess->value);
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\Permissions\SystemPermissions;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');

        Model::shouldBeStrict(! app()->isProduction());

        if (app()->isProduction()) {
            $this->configureProductionUrl();
            $this->configureDbCommands();
        }

        $this->configurePulse();
    }

    /**
     * Força o uso de https na produção.
     */
    private function configureProductionUrl(): void
    {
        URL::forceScheme('https');
        request()->server->set(
            'HTTPS',
            request()->header('X-Forwarded-Proto', 'https') === 'https' ? 'on' : 'off',
        );
    }

    /**
     * Previne comandos de destruição no banco de dados.
     */
    private function configureDbCommands(): void
    {
        DB::prohibitDestructiveCommands();
    }

    /**
     * Configura o Laravel Pulse.
     */
    private function configurePulse(): void
    {
        Gate::define('viewPulse', function (User $user): bool {
            return $user->can(SystemPermissions::PulseAccess);
        });
    }
}

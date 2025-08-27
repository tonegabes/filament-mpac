<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict();

        if (app()->isProduction()) {
            $this->configureProductionUrl();
            $this->configureDbCommands();
        }
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
}

<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\Permissions\SystemPermissions;
use App\Models\User;
use App\Services\Auth\LdapAuthService;
use App\Services\Auth\LdapUserService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Opcodes\LogViewer\Facades\LogViewer;

final class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LdapAuthService::class);
        $this->app->singleton(LdapUserService::class);

        $this->app->singleton(
            abstract: \Filament\Auth\Http\Responses\Contracts\LoginResponse::class,
            concrete: \App\Http\Responses\LoginResponse::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureGates();

        if (app()->isProduction()) {
            LogViewer::auth(fn ($request) => $request->user()?->can(SystemPermissions::LogViewerAccess));
        }
    }

    /**
     * Configure the custom gates.
     */
    private function configureGates(): void
    {
        Gate::before(fn (User $user) => $user->hasRole('TheOneAboveAll') ? true : null);

        Gate::define('viewActivityLogUi', fn (User $user) => $user->can(SystemPermissions::ViewActivityLog));
    }
}

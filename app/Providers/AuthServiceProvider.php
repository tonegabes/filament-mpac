<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\Permissions\SystemPermissions;
use App\Models\User;
use App\Policies\MediaPolicy;
use App\Services\Auth\AuthModeHandlerResolver;
use App\Services\Auth\LdapAuthModeHandler;
use App\Services\Auth\LdapAuthService;
use App\Services\Auth\LdapUserService;
use App\Services\Auth\LocalAuthModeHandler;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Opcodes\LogViewer\Facades\LogViewer;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Media::class => MediaPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LocalAuthModeHandler::class);
        $this->app->singleton(LdapAuthModeHandler::class);
        $this->app->singleton(AuthModeHandlerResolver::class);
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
        $this->registerPolicies();

        $this->configureGates();

        if (! app()->isProduction()) {
            LogViewer::auth(function (Request $request) {

                $user = $request->user();

                if ($user) {
                    return $user->can(SystemPermissions::LogViewerAccess);
                }

                return false;
            });
        }
    }

    /**
     * Configure the custom gates.
     */
    private function configureGates(): void
    {
        Gate::before(fn (User $user) => $user->hasRole('TheOneAboveAll') ? true : null);
    }
}

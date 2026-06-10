<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Components\Navigation\PanelSwitcher;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\Password\ResetPasswordAction;
use App\Filament\Pages\Auth\Password\ResetPasswordRequest;
use App\Filament\Pages\Auth\Register;
use App\Services\Auth\AuthModeHandlerResolver;
use App\Settings\SystemSettings;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $this->configureRegistration($panel);

        return $panel
            ->default()
            ->id('app')
            ->path('')
            ->login(Login::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->viteTheme('resources/css/mpac-theme/index.css')
            ->resources([])
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->userMenuItems(PanelSwitcher::userMenuItems())
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    /**
     * Configure the registration page.
     */
    private function configureRegistration(Panel $panel): Panel
    {
        $authModeHandler = app(AuthModeHandlerResolver::class)->resolveFromConfig();

        if (! $authModeHandler->allowsLocalRegistration()) {
            return $panel;
        }

        $canRegister = false;

        try {
            $canRegister = app(SystemSettings::class)->enable_registration;
        } catch (\Spatie\LaravelSettings\Exceptions\MissingSettings $e) {
            return $panel;
        } catch (\Illuminate\Database\QueryException $e) {
            return $panel;
        } catch (\Exception $e) {
            throw $e;
        }

        if ($canRegister) {
            $panel->registration(Register::class)
                ->passwordReset(
                    ResetPasswordRequest::class,
                    ResetPasswordAction::class,
                )
            ;
        }

        return $panel;
    }
}

<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Enums\AuthMode;
use App\Enums\Panels;
use App\Filament\Components\Navigation\PanelSwitcher;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\Password\ResetPasswordAction;
use App\Filament\Pages\Auth\Password\ResetPasswordRequest;
use App\Filament\Pages\Auth\Register;
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
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Config;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Spatie\LaravelSettings\Exceptions\MissingSettings;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $this->configureLocalAuthFeatures($panel);

        return $panel
            ->default()
            ->id(Panels::App->value)
            ->path(Panels::App->path())
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
     * Configure password reset and optional self-registration for local auth mode.
     */
    private function configureLocalAuthFeatures(Panel $panel): Panel
    {
        $authMode = AuthMode::fromConfig(Config::string('auth.mode'));

        if (! $authMode->allowsLocalRegistration()) {
            return $panel;
        }

        $panel->passwordReset(
            ResetPasswordRequest::class,
            ResetPasswordAction::class,
        );

        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return $panel;
        }

        try {
            $canRegister = app(SystemSettings::class)->enable_registration;
        } catch (MissingSettings|QueryException) {
            return $panel;
        }

        if ($canRegister) {
            $panel->registration(Register::class);
        }

        return $panel;
    }
}

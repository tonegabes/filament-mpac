<?php

namespace App\Providers\Filament;

use Agencetwogether\HooksHelper\HooksHelperPlugin;
use App\Enums\NavGroups;
use App\Enums\Permissions\SystemPermissions;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\Password\ResetPasswordAction;
use App\Filament\Pages\Auth\Password\ResetPasswordRequest;
use App\Filament\Pages\Auth\Register;
use App\Settings\SystemSettings;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Config;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $this->configureRegistration($panel);

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->profile()
            ->brandLogo(fn () => view('components.brand-logo'))
            ->unsavedChangesAlerts()
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->viteTheme('resources/css/app.css')
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\Filament\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\Filament\Pages'
            )
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\Filament\Widgets'
            )
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make(NavGroups::Authorization->value)
                    ->collapsed(true),
                NavigationGroup::make(NavGroups::Settings->value)
                    ->collapsed(true),
                NavigationGroup::make(NavGroups::Tools->value)
                    ->collapsed(true),
            ])
            ->navigationItems([
                NavigationItem::make('Log Viewer')
                    ->group(NavGroups::Tools->value)
                    ->icon(Phosphor::Scroll)
                    ->url('/' . Config::string('log-viewer.route_path'))
                    ->openUrlInNewTab()
                    ->visible(fn () => auth()->user()?->can(SystemPermissions::LogViewerAccess)),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                // HooksHelperPlugin::make(), // Usar apenas quando necessário
            ]);
    }

    /**
     * Configure the registration page.
     */
    private function configureRegistration(Panel $panel): Panel
    {
        if (Config::boolean('auth.ldap.enabled')) {
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

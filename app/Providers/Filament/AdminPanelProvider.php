<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Enums\NavGroups;
use App\Enums\Panels;
use App\Enums\Permissions\SystemPermissions;
use App\Filament\Pages\Auth\Login;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id(Panels::Admin->value)
            ->path(Panels::Admin->path())
            ->login(Login::class)
            ->sidebarWidth('16rem')
            ->profile()
            ->brandLogo(fn () => view('components.brand-logo'))
            ->unsavedChangesAlerts()
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->viteTheme('resources/css/mpac-theme/index.css')
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\Filament\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\Filament\Pages'
            )
            ->pages([])
            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\Filament\Widgets'
            )
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->navigationGroups($this->configureNavigationGroups())
            ->navigationItems($this->configureNavigationItems())
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
            ])
            ->plugins([
            ]);
    }

    /**
     * Configure the navigation groups.
     *
     * @return NavigationGroup[]
     */
    private function configureNavigationGroups(): array
    {
        return [
            NavigationGroup::make(NavGroups::Files->value)
                ->collapsed(true),

            NavigationGroup::make(NavGroups::Authorization->value)
                ->collapsed(true),

            NavigationGroup::make(NavGroups::Settings->value)
                ->collapsed(true),

            NavigationGroup::make(NavGroups::Tools->value)
                ->collapsed(true),
        ];
    }

    /**
     * @return NavigationItem[]
     */
    private function configureNavigationItems(): array
    {
        return [
            NavigationItem::make('Log Viewer')
                ->group(NavGroups::Tools->value)
                ->icon(Phosphor::Scroll)
                ->url('/' . Config::string('log-viewer.route_path'))
                ->openUrlInNewTab()
                ->visible(fn () => Auth::user()?->can(SystemPermissions::LogViewerAccess))
            ,
        ];
    }
}

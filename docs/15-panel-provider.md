# Panel Provider

Este documento explica como configurar e customizar o AdminPanelProvider do Filament.

## 📚 O que é o Panel Provider?

O `AdminPanelProvider` é responsável por configurar o painel administrativo do Filament, incluindo recursos, páginas, widgets, navegação e middleware.

## 🏗️ Estrutura

```
app/Providers/Filament/
├── AdminPanelProvider.php
├── BaseIconsProvider.php
├── OverrideActionsProvider.php
├── OverrideNotificationsProvider.php
└── RenderHooksProvider.php
```

## 📝 Configuração Básica

### AdminPanelProvider

```php
<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->profile()
            ->brandLogo(fn () => view('components.brand-logo'))
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->navigationGroups($this->configureNavigationGroups())
            ->navigationItems($this->configureNavigationItems());
    }
}
```

## 🔍 Descoberta Automática

### Resources

```php
->discoverResources(
    in: app_path('Filament/Resources'),
    for: 'App\Filament\Resources'
)
```

Todos os Resources em `app/Filament/Resources/` são descobertos automaticamente.

### Pages

```php
->discoverPages(
    in: app_path('Filament/Pages'),
    for: 'App\Filament\Pages'
)
```

Todas as Pages em `app/Filament/Pages/` são descobertas automaticamente.

### Widgets

```php
->discoverWidgets(
    in: app_path('Filament/Widgets'),
    for: 'App\Filament\Widgets'
)
```

Todos os Widgets em `app/Filament/Widgets/` são descobertos automaticamente.

## 🧭 Grupos de Navegação

### Configuração

```php
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
```

### Uso em Resources

```php
// ProductResource.php
public static function getNavigationGroup(): string
{
    return NavGroups::Tools->value;
}
```

## 📋 Itens de Navegação Customizados

### Adicionando Itens

```php
private function configureNavigationItems(): array
{
    return [
        NavigationItem::make('Log Viewer')
            ->group(NavGroups::Tools->value)
            ->icon(Phosphor::Scroll)
            ->url('/' . Config::string('log-viewer.route_path'))
            ->openUrlInNewTab()
            ->visible(fn () => Auth::user()?->can(SystemPermissions::LogViewerAccess)),

        NavigationItem::make('Activity Log')
            ->group(NavGroups::Tools->value)
            ->icon(Phosphor::Pulse)
            ->url('/' . Config::string('activitylog-ui.route.prefix'))
            ->openUrlInNewTab()
            ->visible(fn () => Auth::user()?->can(SystemPermissions::ViewActivityLog)),
    ];
}
```

## 🔐 Autenticação

### Login Customizado

```php
->login(Login::class)
```

### Registro

```php
private function configureRegistration(Panel $panel): Panel
{
    if (Config::boolean('auth.ldap.enabled')) {
        return $panel;
    }

    $canRegister = app(SystemSettings::class)->enable_registration ?? false;

    if ($canRegister) {
        $panel->registration(Register::class)
            ->passwordReset(
                ResetPasswordRequest::class,
                ResetPasswordAction::class,
            );
    }

    return $panel;
}
```

## 🎨 Branding

### Logo

```php
->brandLogo(fn () => view('components.brand-logo'))
```

### Cores

```php
->colors([
    'primary' => Color::Indigo,
])
```

### Tema

```php
->viteTheme('resources/css/app.css')
```

## 🛡️ Middleware

### Middleware Padrão

```php
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
```

### Middleware de Autenticação

```php
->authMiddleware([
    Authenticate::class,
])
```

## 📦 Plugins

### Adicionando Plugins

```php
->plugins([
    // Plugins aqui
])
```

## 🎯 Exemplo Completo

```php
// app/Providers/Filament/AdminPanelProvider.php
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
            ->navigationGroups($this->configureNavigationGroups())
            ->navigationItems($this->configureNavigationItems())
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
            ]);
    }
}
```

## 🎯 Boas Práticas

1. **Descoberta Automática**: Use descoberta automática quando possível
2. **Grupos**: Organize navegação com grupos
3. **Permissões**: Verifique permissões em itens de navegação
4. **Configuração**: Separe configurações complexas em métodos privados
5. **Branding**: Customize logo e cores conforme necessário

## 🔗 Próximos Passos

- [Enums e Convenções](09-enums-e-convencoes.md) - Veja NavGroups
- [Sistema de Permissões](07-sistema-permissoes.md) - Configure permissões no panel
- [Páginas Customizadas](05-paginas-customizadas.md) - Crie páginas para o panel

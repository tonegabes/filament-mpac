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
use Filament\Support\Colors\Color;

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
            ->sidebarWidth('16rem')
            ->profile()
            ->brandLogo(fn () => view('components.brand-logo'))
            ->unsavedChangesAlerts()
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->viteTheme('resources/css/mpac-theme/index.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
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
    ];
}
```

## 🔐 Autenticação

### Login Customizado

```php
->login(Login::class)
```

### Registro Condicional (Local x LDAP)

```php
private function configureRegistration(Panel $panel): Panel
{
    $authModeHandler = app(AuthModeHandlerResolver::class)->resolveFromConfig();

    if (! $authModeHandler->allowsLocalRegistration()) {
        return $panel;
    }

    $canRegister = app(SystemSettings::class)->enable_registration;

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

No projeto atual, o controle de registro depende de dois fatores:

1. O modo de autenticação (`auth.mode`): apenas modo local permite auto-registro.
2. A flag `enable_registration` em `SystemSettings`.

## 🎨 Branding

### Logo

```php
->brandLogo(fn () => view('components.brand-logo'))
```

### Cores

```php
->colors([
    'primary' => Color::Emerald,
])
```

### Tema

```php
->viteTheme('resources/css/mpac-theme/index.css')
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
    PreventRequestForgery::class,
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

## 🧱 Providers Complementares

Além do `AdminPanelProvider`, este projeto também usa:

- `BaseIconsProvider`: padroniza o uso de ícones Phosphor em ações e componentes.
- `OverrideActionsProvider`: sobrescreve ações padrão para ícones e UX consistentes.
- `OverrideNotificationsProvider`: padroniza notificações do painel.
- `RenderHooksProvider`: injeta conteúdo em hooks de renderização (`hooks.head-end`).

## 🎯 Boas Práticas

1. **Descoberta Automática**: Use descoberta automática quando possível
2. **Grupos**: Organize navegação com grupos
3. **Permissões**: Verifique permissões em itens de navegação
4. **Configuração**: Separe configurações complexas em métodos privados
5. **Branding**: Mantenha tema e branding sincronizados com `SystemSettings`
6. **Auth Mode**: Centralize decisões de autenticação no `AuthModeHandlerResolver`

## 🔗 Próximos Passos

- [Enums e Convenções](09-enums-e-convencoes.md) - Veja NavGroups
- [Sistema de Permissões](07-sistema-permissoes.md) - Configure permissões no panel
- [Páginas Customizadas](05-paginas-customizadas.md) - Crie páginas para o panel

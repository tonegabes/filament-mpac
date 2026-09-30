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

O painel admin registra resources e pages **explicitamente** (não usa `discoverResources` / `discoverPages` no estado atual):

```php
return $panel
    ->default()
    ->id('admin')
    ->path('admin')
    ->login(Login::class)
    // ...
    ->resources([
        DocumentResource::class,
        ImageResource::class,
        MediaResource::class,
        UserResource::class,
        RoleResource::class,
        PermissionResource::class,
        ActivityResource::class,
    ])
    ->pages([
        ManageSystem::class,
    ])
    ->navigationGroups($this->configureNavigationGroups())
    ->navigationItems($this->configureNavigationItems());
```

`ActivityResource` aparece no grupo `NavGroups::Tools` (“Logs de atividade”). Detalhes: [Logs de Atividade](18-logs-de-atividade.md).

## 🔍 Registro de Resources / Pages

Ao criar um Resource novo, adicione a classe na lista `->resources([...])` do `AdminPanelProvider` (ou reative descoberta automática se o time decidir padronizar assim).

O mesmo vale para páginas customizadas em `->pages([...])`.

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
        NavigationItem::make('Pulse')
            ->group(NavGroups::Tools->value)
            ->icon(Phosphor::Pulse)
            ->url('/' . Config::string('pulse.path'))
            ->openUrlInNewTab()
            ->visible(fn () => Auth::user()?->can(SystemPermissions::PulseAccess)),
    ];
}
```

Além da permissão Spatie no item de menu, o Pulse exige o gate `viewPulse` definido em `AppServiceProvider::configurePulse()`.

Não há mais item de Log Viewer / Telescope no menu (pacotes removidos). Auditoria de domínio: `ActivityResource`. Logs de runtime: `storage/logs`.

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

### OverrideActionsProvider + AppServiceProvider

Registro em `bootstrap/providers.php`:

- `OverrideActionsProvider` — `CreateAction` / `EditAction` / `DeleteAction` com ícones Phosphor (`Plus`, `PencilSimpleLine`, `Trash`) e cores Indigo/Rose para Edit/Delete.
- `AppServiceProvider::configureComponents()` — `CreateAction::configureUsing(...)->iconButton()`, para o header “Criar” ser só ícone em todas as listagens.

Os dois `configureUsing` de `CreateAction` se acumulam: ícone do override + estilo `iconButton` do AppServiceProvider.

O mesmo `AppServiceProvider` também define strict Eloquent fora de produção, HTTPS/DB guards em produção e o gate do Pulse — detalhes em [Setup e Troubleshooting](17-setup-dependencias-e-troubleshooting.md).

Detalhes de uso e override local: [Actions Customizadas](12-actions-customizadas.md).

## 🎯 Boas Práticas

1. **Registro explícito**: Inclua novos Resources/Pages na lista do provider (ou padronize descoberta se o time migrar).
2. **Grupos**: Organize navegação com grupos
3. **Permissões**: Verifique permissões em itens de navegação
4. **Configuração**: Separe configurações complexas em métodos privados
5. **Branding**: Mantenha tema e branding sincronizados com `SystemSettings`
6. **Auth Mode**: Centralize decisões de autenticação no `AuthModeHandlerResolver`

## 🔗 Próximos Passos

- [Enums e Convenções](09-enums-e-convencoes.md) - Veja NavGroups
- [Sistema de Permissões](07-sistema-permissoes.md) - Configure permissões no panel
- [Páginas Customizadas](05-paginas-customizadas.md) - Crie páginas para o panel
- [Logs de Atividade](18-logs-de-atividade.md) - ActivityResource no grupo Tools

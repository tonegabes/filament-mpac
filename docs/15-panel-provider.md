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

Resources e pages são registrados **explicitamente** (não use `discoverResources` / `discoverPages` neste painel):

```php
// app/Providers/Filament/AdminPanelProvider.php (trecho)
return $panel
    ->default()
    ->id(Panels::Admin->value)
    ->path(Panels::Admin->path())
    ->login(Login::class)
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
    ->widgets([
        AccountWidget::class,
        FilamentInfoWidget::class,
    ])
    ->userMenuItems(PanelSwitcher::userMenuItems())
    ->navigationGroups($this->configureNavigationGroups())
    ->navigationItems($this->configureNavigationItems());
```

Todo Resource novo precisa ser adicionado à lista `->resources([...])`.

## 🔍 Registro explícito vs descoberta

Neste projeto o painel **admin** lista Resources/Pages à mão. A descoberta automática (`discoverResources` / `discoverPages`) **não** está em uso no `AdminPanelProvider` atual.

Widgets padrão (`AccountWidget`, `FilamentInfoWidget`) também são registrados explicitamente.

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

O grupo **Ferramentas** (`NavGroups::Tools`) também inclui `ActivityResource` (“Logs de atividade”), registrado em `->resources([...])` — ver [Logs de Atividade](19-logs-de-atividade.md).

## 🔐 Autenticação

### Login Customizado

No painel admin:

```php
->login(Login::class)
```

### Registro Condicional (Local x LDAP)

Registro e password-reset vivem em `AppPanelProvider::configureRegistration()` (não no Admin):

```php
// app/Providers/Filament/AppPanelProvider.php
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

Controle de registro depende de dois fatores:

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

1. **Registro explícito**: adicione Resources novos em `->resources([...])` do `AdminPanelProvider`
2. **Grupos**: Organize navegação com grupos
3. **Permissões**: Verifique permissões em itens de navegação
4. **Configuração**: Separe configurações complexas em métodos privados
5. **Branding**: Mantenha tema e branding sincronizados com `SystemSettings`
6. **Auth Mode**: Centralize decisões de autenticação no `AuthModeHandlerResolver`

## 🔗 Próximos Passos

- [Enums e Convenções](09-enums-e-convencoes.md) - Veja NavGroups
- [Sistema de Permissões](07-sistema-permissoes.md) - Configure permissões no panel
- [Logs de Atividade](19-logs-de-atividade.md) - Resource em Ferramentas
- [Páginas Customizadas](05-paginas-customizadas.md) - Crie páginas para o panel

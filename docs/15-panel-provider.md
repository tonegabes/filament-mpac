# Panel Provider

Este documento explica como configurar e customizar os Panel Providers do Filament (`AdminPanelProvider` e `AppPanelProvider`).

## 📚 O que é o Panel Provider?

Os Panel Providers configuram cada painel Filament: resources, páginas, widgets, navegação, auth e middleware.

## 🏗️ Estrutura

```
app/Providers/Filament/
├── AdminPanelProvider.php
├── AppPanelProvider.php
├── BaseIconsProvider.php
├── OverrideActionsProvider.php
├── OverrideNotificationsProvider.php
└── RenderHooksProvider.php
```

## 📝 AdminPanelProvider (resumo)

Painel `admin` (`Panels::Admin`), path `/admin`, login customizado, tema MPAC, resources e páginas **registrados explicitamente**.

Trecho relevante:

```php
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
    ->userMenuItems(PanelSwitcher::userMenuItems())
    ->navigationGroups($this->configureNavigationGroups())
    ->navigationItems($this->configureNavigationItems())
    ->plugins([
        FilamentFlexFieldsPlugin::make(),
    ]);
```

## 🔍 Registro de Resources e Pages

O admin **não** usa `discoverResources` / `discoverPages`. Novos Resources devem ser adicionados ao array `->resources([...])` em `AdminPanelProvider`.

Widgets ainda podem ser descobertos ou listados explicitamente conforme o provider; no admin atual, widgets padrão são listados via `->widgets([...])`.

## 🧭 Grupos de Navegação

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
public static function getNavigationGroup(): string
{
    return NavGroups::Tools->value;
}
```

## 📋 Itens de Navegação Customizados

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

O grupo **Ferramentas** (`NavGroups::Tools`) também inclui `ActivityResource` (“Logs de atividade”) via registro explícito — ver [Logs de Atividade](18-logs-de-atividade.md).

Log Viewer e Telescope foram removidos: não há mais itens de menu nem permissões para eles. Logs de aplicação: `storage/logs` / `composer dev` (pail).

## 🔐 Autenticação

### Login (admin)

```php
->login(Login::class)
```

### Registro condicional (painel `app`)

O auto-registro vive no `AppPanelProvider::configureRegistration()`, não no admin:

1. Modo de autenticação (`auth.mode`): apenas modo local permite auto-registro.
2. Flag `enable_registration` em `SystemSettings`.

```php
// AppPanelProvider
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

Middleware padrão do Filament (cookies, session, CSRF, bindings, ícones, dispatch) + `authMiddleware([Authenticate::class])`.

## 📦 Plugins

```php
->plugins([
    FilamentFlexFieldsPlugin::make(),
])
```

## 🧱 Providers Complementares

Além dos Panel Providers, este projeto também usa:

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

1. **Registro explícito**: ao criar um Resource novo, inclua-o em `->resources([...])` do panel correspondente
2. **Grupos**: organize navegação com `NavGroups`
3. **Permissões**: verifique permissões em itens de navegação
4. **Configuração**: separe configurações complexas em métodos privados
5. **Branding**: mantenha tema e branding sincronizados com `SystemSettings`
6. **Auth Mode**: centralize decisões de autenticação no `AuthModeHandlerResolver`

## 🔗 Próximos Passos

- [Enums e Convenções](09-enums-e-convencoes.md) — NavGroups / Panels
- [Sistema de Permissões](07-sistema-permissoes.md)
- [Logs de Atividade](18-logs-de-atividade.md)
- [Páginas Customizadas](05-paginas-customizadas.md)
- [Setup e Troubleshooting](17-setup-dependencias-e-troubleshooting.md)

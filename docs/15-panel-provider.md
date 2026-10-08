# Panel Provider

Este documento explica como configurar os Panel Providers do Filament (`AdminPanelProvider` e `AppPanelProvider`).

## 📚 O que é o Panel Provider?

Os Panel Providers configuram cada painel Filament: resources, páginas, widgets, navegação, auth e middleware.

Registro em `bootstrap/providers.php`:

- `AdminPanelProvider`
- `AppPanelProvider`
- providers complementares (ícones, overrides, render hooks)

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

## 🪟 Dois painéis, um default

| Painel | Provider | Path | `->default()` | Conteúdo principal |
| --- | --- | --- | --- | --- |
| `app` (`Panels::App`) | `AppPanelProvider` | `/` | **sim** (único) | Dashboard; registro/reset condicionais |
| `admin` (`Panels::Admin`) | `AdminPanelProvider` | `/admin` | não | Resources CRUD, settings, Pulse nav |

**Regra:** apenas o painel `app` chama `->default()`. O admin deixou de ser default para evitar conflito de painel padrão no Filament (dois `->default()` quebram a resolução).

Garantia automatizada: `tests/Unit/Filament/DefaultPanelTest.php` — espera exatamente um painel com `isDefault()`.

Acesso: `User::canAccessPanel()` via `PanelPermissions` (`panels.view.app` / `panels.view.admin`). Troca no menu: `PanelSwitcher::userMenuItems()`.

## 📝 AdminPanelProvider

Painel administrativo: login customizado, tema MPAC, resources e páginas **registrados explicitamente** (sem `discoverResources` / `discoverPages`).

```php
return $panel
    ->id(Panels::Admin->value)
    ->path(Panels::Admin->path())
    ->login(Login::class)
    // sem ->default()
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

### Registro de Resources e Pages

Novos Resources admin devem ser adicionados a `->resources([...])` em `AdminPanelProvider`. O scaffold `make:model-plus` não substitui esse passo se o Resource não for ligado ao provider.

Widgets do admin são listados via `->widgets([...])` (Account / FilamentInfo).

## 📝 AppPanelProvider

Painel público/app na raiz (`path('')`), cor Amber, resources vazios, página `Dashboard`.

```php
return $panel
    ->default()
    ->id('app')
    ->path('')
    ->login(Login::class)
    ->resources([])
    ->pages([
        Dashboard::class,
    ])
    ->userMenuItems(PanelSwitcher::userMenuItems());
```

## 🧭 Grupos e itens de navegação (admin)

```php
private function configureNavigationGroups(): array
{
    return [
        NavigationGroup::make(NavGroups::Files->value)->collapsed(true),
        NavigationGroup::make(NavGroups::Authorization->value)->collapsed(true),
        NavigationGroup::make(NavGroups::Settings->value)->collapsed(true),
        NavigationGroup::make(NavGroups::Tools->value)->collapsed(true),
    ];
}
```

Em Resources:

```php
public static function getNavigationGroup(): string
{
    return NavGroups::Tools->value;
}
```

Item customizado Pulse:

```php
NavigationItem::make('Pulse')
    ->group(NavGroups::Tools->value)
    ->icon(Phosphor::Pulse)
    ->url('/' . Config::string('pulse.path'))
    ->openUrlInNewTab()
    ->visible(fn () => Auth::user()?->can(SystemPermissions::PulseAccess));
```

Além da permissão Spatie no menu, o Pulse exige o gate `viewPulse` em `AppServiceProvider::configurePulse()`.

O grupo **Ferramentas** também inclui `ActivityResource` via registro explícito — ver [Modelos e Relacionamentos](14-modelos-e-relacionamentos.md).

Log Viewer e Telescope foram removidos. Logs de aplicação: `storage/logs` / `composer dev` (pail).

## 🔐 Autenticação

### Login

Ambos os painéis usam `->login(Login::class)`.

### Registro e reset (somente `app`)

O auto-registro vive em `AppPanelProvider::configureRegistration()`, **não** no admin:

1. Modo de autenticação (`auth.mode`): só modo local permite auto-registro (`AuthModeHandlerResolver`).
2. Flag `enable_registration` em `SystemSettings`.
3. Se settings ainda não existem (migrate/seed incompleto), captura `MissingSettings` ou `QueryException` e **não** habilita registro — evita falha no boot.

```php
private function configureRegistration(Panel $panel): Panel
{
    $authModeHandler = app(AuthModeHandlerResolver::class)->resolveFromConfig();

    if (! $authModeHandler->allowsLocalRegistration()) {
        return $panel;
    }

    $canRegister = false;

    try {
        $canRegister = app(SystemSettings::class)->enable_registration;
    } catch (MissingSettings $e) {
        return $panel;
    } catch (QueryException $e) {
        return $panel;
    } catch (\Exception $e) {
        throw $e;
    }

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

- Logo: `->brandLogo(fn () => view('components.brand-logo'))` (admin)
- Cores: Emerald (admin) / Amber (app)
- Tema: `->viteTheme('resources/css/mpac-theme/index.css')` nos dois

## 🛡️ Middleware

Middleware padrão do Filament (cookies, session, CSRF, bindings, ícones, dispatch) + `authMiddleware([Authenticate::class])` em ambos.

## 📦 Plugins

Admin registra `FilamentFlexFieldsPlugin::make()`.

## 🧱 Providers complementares

- `BaseIconsProvider`: ícones Phosphor padrão
- `OverrideActionsProvider`: Create/Edit/Delete com ícones e cores consistentes
- `OverrideNotificationsProvider`: notificações padronizadas
- `RenderHooksProvider`: hooks (`hooks.head-end`)

### OverrideActionsProvider + AppServiceProvider

- `OverrideActionsProvider` — ícones Phosphor (`Plus`, `PencilSimpleLine`, `Trash`) e cores Indigo/Rose
- `AppServiceProvider::configureComponents()` — `CreateAction::configureUsing(...)->iconButton()`

Os dois `configureUsing` de `CreateAction` se acumulam. Detalhes: [Actions Customizadas](12-actions-customizadas.md) e [Setup](17-setup-dependencias-e-troubleshooting.md).

## ⚠️ Pitfalls

| Sintoma | Causa | Ação |
| --- | --- | --- |
| Resource novo “não aparece” no admin | não está em `->resources([...])` | registrar em `AdminPanelProvider` |
| Erro / ambiguidade de painel default | mais de um `->default()` | só `AppPanelProvider` deve ser default; rode `DefaultPanelTest` |
| Registro local ausente | LDAP ou `enable_registration=false` | conferir `auth.mode` e SystemSettings |
| Boot falha lendo settings | migrate/seed incompleto | o catch de `MissingSettings`/`QueryException` deve engolir; não remova |

## 🎯 Boas práticas

1. **Um default**: apenas `app`
2. **Registro explícito** de Resources no panel correspondente
3. **Grupos** com `NavGroups`
4. **Permissões** em itens de navegação e `canAccessPanel`
5. **Auth** centralizada no `AuthModeHandlerResolver` + settings
6. **Configuração** complexa em métodos privados do provider

## 🔗 Próximos passos

- [Enums e Convenções](09-enums-e-convencoes.md) — NavGroups / Panels
- [Sistema de Permissões](07-sistema-permissoes.md)
- [Páginas Customizadas](05-paginas-customizadas.md)
- [Testes](13-testes.md)
- [Setup e Troubleshooting](17-setup-dependencias-e-troubleshooting.md)

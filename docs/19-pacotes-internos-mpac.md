# Pacotes Internos MPAC (`mpac/*`)

O template `filament-mpac` adota uma arquitetura de **esqueleto fino**: toda a infraestrutura institucional (autenticação, controle de acesso, auditoria, biblioteca de arquivos, identidade visual, utilitários e qualidade de código) reside em pacotes Composer versionados da organização MPAC.

## 📦 Pacotes Consumidos pelo Template

| Pacote | Tipo | Função no Template | Documentação Local |
| :--- | :--- | :--- | :--- |
| `mpac/filament-core` | Runtime | Meta-pacote e `FilamentCorePlugin` usado em `AdminPanelProvider` e `AppPanelProvider` | [README](../../../codes/packages/filament-core/README.md) |
| `mpac/filament-auth-external` | Runtime | Login OAuth2/OIDC via Socialite habilitado via `FilamentAuthExternalPlugin` | [README](../../../codes/packages/filament-auth-external/README.md) |
| `mpac/laravel-essentials` | Runtime (via core) | Helpers, `BetterEnum`, `HasBlameable`, `HasIsActiveScope`, hardening | [README](../../../codes/packages/laravel-essentials/README.md) |
| `mpac/filament-auth` | Runtime (via core) | Login Local/LDAP/Híbrido, registro, reset de senha e `LoginResponse` | [README](../../../codes/packages/filament-auth/README.md) |
| `mpac/filament-access-control` | Runtime (via core) | `UserResource`, `RoleResource`, `PermissionResource`, `ResourcePolicy`, `PermissionRegistry`, `PanelSwitcher` | [README](../../../codes/packages/filament-access-control/README.md) |
| `mpac/filament-audit` | Runtime (via core) | `ActivityResource`, `ActivitiesRelationManager`, `ViewActivitiesAction`, `ActivityLog` | [README](../../../codes/packages/filament-audit/README.md) |
| `mpac/filament-file-library` | Runtime (via core) | `Document`, `Image`, `MediaResource`, discos privados e download autenticado | [README](../../../codes/packages/filament-file-library/README.md) |
| `mpac/filament-branding` | Runtime (via core) | Tema CSS Tailwind v4, Phosphor Icons, `ActionOverrides`, `SystemSettings`, `ManageSystem` | [README](../../../codes/packages/filament-branding/README.md) |
| `mpac/php-quality` | Dev | Regras base do Pint e PHPStan Nível 8 (`phpstan.neon`) | [README](../../../codes/packages/php-quality/README.md) |
| `mpac/testing` | Dev | `MpacTestCase` e helpers `actingAsDeveloper()`, `actingAsAdmin()`, `actingAsUser()` | [README](../../../codes/packages/testing/README.md) |

> **Índice completo do monorepo de pacotes:** [`e:\codes\packages\README.md`](../../../codes/packages/README.md).

---

## 🛠️ Como o App Consome e Customiza os Pacotes

### 1. Registro nos PanelProviders

Tanto [AdminPanelProvider.php](../app/Providers/Filament/AdminPanelProvider.php) quanto [AppPanelProvider.php](../app/Providers/Filament/AppPanelProvider.php) usam:

```php
->plugins([
    FilamentCorePlugin::make(),
    FilamentAuthExternalPlugin::make(),
])
```

Para customizar qualquer módulo em um painel específico, passe closures para `FilamentCorePlugin`:

```php
FilamentCorePlugin::make()
    ->auth(configureUsing: fn ($auth) => $auth->mode(\Mpac\FilamentAuth\Enums\AuthMode::Local))
    ->fileLibrary(false);
```

### 2. Permissões e Policies de Domínio no App

- Crie seus enums de permissão em `app/Enums/Permissions/` implementando `Mpac\FilamentAccessControl\Contracts\ResourcePermissions` com `use DefinesResourcePermissions`.
- Estenda `Mpac\FilamentAccessControl\Policies\ResourcePolicy` em `app/Policies/`.
- Rode `php artisan mpac:permissions:sync` para sincronizar em lote para o banco.

### 3. Auditoria em Models do App

- Implemente `Mpac\FilamentAudit\Contracts\HasActivityLabel` no Model e chame `ActivityLog::registerSubject(MeuModel::class, 'Rótulo')` no `AppServiceProvider::boot()`.
- Adicione `ActivitiesRelationManager::class` ou `ViewActivitiesAction::make()` no Resource do app.

### 4. Qualidade e Testes no App

- `phpstan.neon` inclui `vendor/mpac/php-quality/phpstan.neon`.
- `tests/TestCase.php` estende `Mpac\Testing\MpacTestCase`.

---

## 🤖 Governança Automática de Documentação e Changelog

Quando qualquer agente de IA editar um pacote interno em `e:\codes\packages\*`:
- Regras hierárquicas (`AGENTS.md` na raiz dos pacotes e em cada pacote), guidelines Boost (`.ai/guidelines/mpac-packages-governance.md`), skill `mpac-package-docs` e o lifecycle hook `Stop` em `.agents/hooks.json` garantem a atualização simultânea de `CHANGELOG.md` (`[Unreleased]`), `README.md` e `docs/`.

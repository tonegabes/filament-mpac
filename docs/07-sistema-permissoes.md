# Sistema de Permissões

Este documento descreve o sistema atual de permissões usando `spatie/laravel-permission` (^8).

## 📚 Visão Geral

As permissões do projeto são centralizadas em enums:

```text
app/Enums/Permissions/
├── UserPermissions.php
├── RolePermissions.php
├── PermissionPermissions.php
├── SystemPermissions.php
├── PanelPermissions.php
├── DocumentPermissions.php
├── ImagePermissions.php
├── MediaPermissions.php
├── ActivityPermissions.php
└── WildcardPermissions.php
```

Enums de recurso CRUD (`User`, `Role`, `Permission`, `Document`, `Image`, `Media`, `Activity`) implementam `App\Contracts\ResourcePermissions` e usam o trait `App\Traits\ResolvesResourcePermissions` para mapear ações de policy (`viewAny`, `update`, `deleteAny`, …) para o case correspondente (`ViewAny`, `Update`, `Delete`, …).

## 🔐 SystemPermissions

- `system.*`
- `system.settings.manage`
- `system.pulse.access`

## 🧭 PanelPermissions

- `panels.*`
- `panels.view.admin`
- `panels.view.app`

Mapeamento painel → permissão via `PanelPermissions::fromPanel($panel)` e o enum `App\Enums\Panels` (`admin`, `app`).

## 👤 Permissões por módulo

Módulos com enum CRUD completo (via `ResourcePermissions`):

- `users.*`
- `roles.*`
- `permissions.*`
- `documents.*`
- `images.*`
- `media.*`

Auditoria (somente leitura):

- `activities.*`
- `activities.view.any`
- `activities.view`

Wildcard:

- `*` (`WildcardPermissions::All`) — atribuído ao role `Developer` no seeder

## 🌱 Seeders oficiais

### Cadeia `DatabaseSeeder`

1. `ProductionSeeder` → `PermissionSeeder` + `RoleSeeder` (sempre)
2. `DevelopmentSeeder` → `UserSeeder` (**somente fora de produção**; aborta se `app()->isProduction()`)

Logging de activity fica desligado durante o seed (`activity()->disableLogging()` / `enableLogging()`).

### PermissionSeeder

`database/seeders/PermissionSeeder.php` apenas chama:

```bash
php artisan mpac:permissions:sync
```

### RoleSeeder

`database/seeders/RoleSeeder.php` cria e sincroniza:

- `UserRole::Developer` (`Desenvolvedor`)
- `UserRole::Admin` (`Administrador`)
- `UserRole::User` (`Usuário`)

Distribuição atual:

| Role | Permissões |
| --- | --- |
| `Developer` | `*` (wildcard) |
| `Admin` | painel admin + users + documents + images + media + activities |
| `User` | `panels.view.app` |

Role padrão para novos usuários (registro local / LDAP sem role): `config('auth.default_role')` → `UserRole::default()` = `User`.

## 🛠️ Comandos operacionais

### `mpac:permissions:sync`

Sincroniza todos os backed enums em `app/Enums/Permissions` para a tabela `permissions` (upsert por `name` + `guard_name`). Limpa o cache do Spatie antes e depois.

```bash
php artisan mpac:permissions:sync
php artisan mpac:permissions:sync --prune   # remove permissões órfãs do banco
```

Constraints:

- Descoberta automática por arquivo em `app/Enums/Permissions` (não precisa registrar manualmente).
- `--prune` apaga nomes que não existem mais nos enums — use com cuidado em ambientes com permissões manuais.
- Testes de referência: `tests/Feature/Console/SyncPermissionsCommandTest.php`

### `mpac:create-admin`

Cria ou atualiza um administrador local (`updateOrCreate` por `username`), marca `is_active = true` e sincroniza roles:

```bash
php artisan mpac:create-admin
php artisan mpac:create-admin --name="..." --username=admin --email=admin@example.com --password='...'
php artisan mpac:create-admin ... --developer   # Admin + Developer
```

Sem `--developer`, o role atribuído é só `Administrador`. Opções interativas usam Laravel Prompts quando flags omitidas. Teste: `tests/Feature/Console/CreateAdminCommandTest.php`.

## 🧭 Acesso ao painel Filament

```php
public function canAccessPanel(?Panel $panel): bool
{
    if (! $this->is_active || $panel === null) {
        return false;
    }

    $panelPermission = PanelPermissions::fromPanel($panel);
    $permission = $panelPermission !== null
        ? $panelPermission->value
        : "panels.view.{$panel->getId()}";

    return $this->can($permission);
}
```

Usuário inativo nunca entra no painel, mesmo com permissão correta.

## 🛡️ Superadmin e gestão de usuários

Não há `Gate::before` global. `AuthServiceProvider::configureGates()` está vazio.

Hierarquia no model `User`:

| Método | Intenção |
| --- | --- |
| `isSuperAdmin()` | role `Developer` **ou** permissão wildcard `*` (direta/via role) |
| `canAssignRole(Role)` | superadmin livre; demais não atribuem `Developer` nem wildcard / permissões que não possuem |
| `canManageUser(User)` | só gerencia alvos cujos roles o ator poderia atribuir |
| `isLastDeveloper()` | protege o último Developer de exclusão |

Essas regras reforçam `UserPolicy` (update/delete/forceDelete).

## 🎯 Uso prático

### Em páginas

```php
public static function canAccess(): bool
{
    return auth()->user()?->can(SystemPermissions::SystemSettingsManage) ?? false;
}
```

### Em itens de navegação

```php
NavigationItem::make('Pulse')
    ->visible(fn () => Auth::user()?->can(SystemPermissions::PulseAccess));
```

O gate Laravel `viewPulse` (em `AppServiceProvider`) também exige `SystemPermissions::PulseAccess`.

### Em policies de recurso

Prefira estender `App\Policies\ResourcePolicy` e apontar `$permissions` para o enum — ver [Policies](08-policies-e-autorizacao.md).

## ⚠️ Pitfalls

1. Após criar um enum de permissões, rode `php artisan mpac:permissions:sync` (ou `migrate --seed` / `composer fresh:db` em local).
2. Atualize `RoleSeeder` ao adicionar módulo que Admin/User devem enxergar.
3. Cache Spatie: o comando de sync já chama `forgetCachedPermissions()`; em testes manuais use o `PermissionRegistrar`.
4. `--prune` remove permissões ausentes dos enums — não use cegamente em produção sem revisar.
5. Não use mais `UserRole::Operator` — foi substituído por `UserRole::User`.
6. Não documente / implemente bypass `TheOneAboveAll` / `Gate::before` — não existe no código atual.

## 🎯 Boas Práticas

1. Novas permissões só via enum (+ sync).
2. Enums de Resource devem implementar `ResourcePermissions` + `ResolvesResourcePermissions`.
3. Evitar strings fixas em checks de permissão.
4. Manter o padrão `module.action` (ex.: `users.view.any`).
5. Testar permissões com feature/policy tests e os testes de console quando alterar sync/admin.

## 🔗 Próximos Passos

- [Policies e Autorização](08-policies-e-autorizacao.md)
- [Enums e Convenções](09-enums-e-convencoes.md)
- [Setup e Troubleshooting](17-setup-dependencias-e-troubleshooting.md)
- [Testes](13-testes.md)

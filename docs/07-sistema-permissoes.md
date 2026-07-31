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
└── WildcardPermissions.php
```

## 🔐 SystemPermissions

- `system.*` (`SystemPermissions::All`)
- `system.log-viewer.access` (`LogViewerAccess`) — rota `/log-viewer`
- `system.pulse.access` (`PulseAccess`) — rota `/pulse` (gate `viewPulse`)
- `system.settings.manage` (`SystemSettingsManage`)

## 🧭 PanelPermissions

- `panels.*`
- `panels.view.admin`
- `panels.view.app`

Mapeamento painel → permissão via `PanelPermissions::fromPanel($panel)` e o enum `App\Enums\Panels` (`admin`, `app`).

## 👤 Permissões por módulo

Módulos com enum CRUD completo:

- `users.*`
- `roles.*`
- `permissions.*`
- `documents.*`
- `images.*`

## 🌱 Seeders oficiais

### PermissionSeeder

`database/seeders/PermissionSeeder.php` popula permissões descobrindo automaticamente os enums em `app/Enums/Permissions`.

### RoleSeeder

`database/seeders/RoleSeeder.php` cria e sincroniza:

- `UserRole::Developer` (`Desenvolvedor`)
- `UserRole::Admin` (`Administrador`)
- `UserRole::User` (`Usuário`)

Distribuição atual:

| Role | Permissões |
| --- | --- |
| `Developer` | `*` (todas) |
| `Admin` | painel admin + users + documents + images |
| `User` | `panels.view.app` |

Role padrão para novos usuários (registro local / LDAP sem role): `config('auth.default_role')` → `UserRole::default()` = `User`.

## 🧭 Acesso ao painel Filament

```php
public function canAccessPanel(?Panel $panel): bool
{
    $permission = PanelPermissions::fromPanel($panel);

    if ($permission === null) {
        return false;
    }

    return $this->can($permission);
}
```

## 🛡️ Gate global de superusuário

No `AuthServiceProvider`, existe bypass por role:

```php
Gate::before(fn (User $user) => $user->hasRole('TheOneAboveAll') ? true : null);
```

Esse papel não é criado no `RoleSeeder` padrão; use com cuidado em ambientes controlados.

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
NavigationItem::make('Log Viewer')
    ->visible(fn () => Auth::user()?->can(SystemPermissions::LogViewerAccess));

NavigationItem::make('Pulse')
    ->visible(fn () => Auth::user()?->can(SystemPermissions::PulseAccess));
```

Autorização das ferramentas:

- Log Viewer: `AuthServiceProvider` → `LogViewer::auth(...)` + `SystemPermissions::LogViewerAccess`
- Pulse: `AppServiceProvider::configurePulse()` → `Gate::define('viewPulse', ...)` + `SystemPermissions::PulseAccess`

## ⚠️ Pitfalls

1. Após criar um enum de permissões, rode o seeder (ou `migrate:fresh --seed` em local).
2. Cache de permissões: limpe com `PermissionRegistrar::forgetCachedPermissions()` nos testes.
3. Upgrade para `spatie/laravel-permission` 8: revise breaking changes de API se customizar guards/teams.
4. Não use mais `UserRole::Operator` — foi substituído por `UserRole::User`.

## 🎯 Boas Práticas

1. Sempre adicionar novas permissões via enum + seeder.
2. Evitar strings fixas em checks de permissão.
3. Revisar `RoleSeeder` quando criar novo módulo.
4. Manter o padrão `module.action` para consistência.
5. Testar permissões com testes de feature/policy.

## 🔗 Próximos Passos

- [Policies e Autorização](08-policies-e-autorizacao.md)
- [Enums e Convenções](09-enums-e-convencoes.md)
- [Testes](13-testes.md)

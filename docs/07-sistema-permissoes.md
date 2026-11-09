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

## 🔐 SystemPermissions

- `system.*`
- `system.settings.manage`
- `system.pulse.access` (`PulseAccess` — menu Pulse + gate `viewPulse`)

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
- `media.*`

Módulo view-only:

- `activities.*` / `activities.view.any` / `activities.view` (`ActivityPermissions`)

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

## 🛡️ Gates

`AuthServiceProvider::configureGates()` está vazio — não há bypass global de role.

Pulse usa o gate `viewPulse` em `AppServiceProvider` (permissão `SystemPermissions::PulseAccess`). Policies de `Activity`/`Media` (models Spatie) estão mapeadas em `AuthServiceProvider::$policies` — ver [Policies](08-policies-e-autorizacao.md).

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

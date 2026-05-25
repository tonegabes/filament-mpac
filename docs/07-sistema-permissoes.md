# Sistema de Permissões

Este documento descreve o sistema atual de permissões usando `spatie/laravel-permission`.

## 📚 Visão Geral

As permissões do projeto são centralizadas em enums:

```text
app/Enums/Permissions/
├── UserPermissions.php
├── RolePermissions.php
├── PermissionPermissions.php
└── SystemPermissions.php
```

## 🔐 SystemPermissions (escopo atual)

Permissões de sistema disponíveis:

- `system.panels`
- `system.panels.view.admin`
- `system.panels.view.operator`
- `system.log-viewer.access`
- `system.settings.manage`

## 👤 Permissões por módulo

Atualmente os módulos com enum completo de CRUD são:

- `users.*`
- `roles.*`
- `permissions.*`

Observação: no estado atual, o domínio de arquivos (`documents`, `images`, `media`) não possui enum de permissões dedicado.

## 🌱 Seeders oficiais

### PermissionSeeder

`database/seeders/PermissionSeeder.php` popula permissões usando os enums:

```php
$permissionsBag = [
    ...SystemPermissions::cases(),
    ...UserPermissions::cases(),
    ...RolePermissions::cases(),
    ...PermissionPermissions::cases(),
];
```

### RoleSeeder

`database/seeders/RoleSeeder.php` cria e sincroniza:

- `Roles::Developer`
- `Roles::Admin`
- `Roles::Operator`

Distribuição atual:

- `Developer`: permissões completas de system/users/roles/permissions.
- `Admin`: acesso ao painel admin + permissões de usuários.
- `Operator`: acesso ao painel operator.

## 🧭 Acesso ao painel Filament

O acesso ao painel é validado no `User::canAccessPanel()`:

```php
public function canAccessPanel(?Panel $panel): bool
{
    $panelId = $panel?->getId();

    return $this->can("system.panels.view.{$panelId}");
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
```

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

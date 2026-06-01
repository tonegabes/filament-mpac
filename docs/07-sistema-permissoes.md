# Sistema de Permissões

Este documento descreve o sistema atual de permissões usando `spatie/laravel-permission`.

## 📚 Visão Geral

As permissões do projeto são centralizadas em enums:

```text
app/Enums/Permissions/
├── UserPermissions.php
├── RolePermissions.php
├── PermissionPermissions.php
├── SystemPermissions.php
└── PanelPermissions.php
```

## 🔐 SystemPermissions (escopo atual)

Permissões de sistema disponíveis:

- `system`
- `system.log-viewer.access`
- `system.settings.manage`

## 🧭 PanelPermissions

Permissões de acesso aos painéis Filament:

- `panels`
- `panels.view.admin`

O enum `PanelPermissions` também centraliza o mapeamento de `Panel` para permissão:

```php
PanelPermissions::fromPanel($panel);
```

## 👤 Permissões por módulo

Atualmente os módulos com enum completo de CRUD são:

- `users.*`
- `roles.*`
- `permissions.*`

Observação: no estado atual, o domínio de arquivos (`documents`, `images`, `media`) não possui enum de permissões dedicado.

## 🌱 Seeders oficiais

### PermissionSeeder

`database/seeders/PermissionSeeder.php` popula permissões descobrindo automaticamente os enums em `app/Enums/Permissions`:

```php
foreach (File::allFiles(app_path('Enums/Permissions')) as $file) {
    // Cada enum backed encontrado contribui com seus cases.
}
```

### RoleSeeder

`database/seeders/RoleSeeder.php` cria e sincroniza:

- `Roles::Developer`
- `Roles::Admin`
- `Roles::Operator`

Distribuição atual:

- `Developer`: permissões completas de system/panels/users/roles/permissions.
- `Admin`: acesso ao painel admin + permissões de usuários.
- `Operator`: acesso ao painel admin.

## 🧭 Acesso ao painel Filament

O acesso ao painel é validado no `User::canAccessPanel()`:

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

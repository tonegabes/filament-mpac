# Sistema de Permissões

Este documento explica como funciona o sistema de permissões usando Spatie Laravel Permission e como criar e gerenciar permissões no projeto.

## 📚 Visão Geral

O projeto usa **Spatie Laravel Permission** para gerenciar permissões e roles. O sistema está organizado através de **Enums** que facilitam a manutenção e uso das permissões.

## 🏗️ Estrutura de Permissões

As permissões são organizadas em Enums por módulo:

```
app/Enums/Permissions/
├── UserPermissions.php
├── RolePermissions.php
├── PermissionPermissions.php
└── SystemPermissions.php
```

## 📝 Criando um Enum de Permissões

### Estrutura Básica

```php
<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

enum ProductPermissions: string
{
    case All = 'products';
    case View = 'products.view';
    case Create = 'products.create';
    case Update = 'products.update';
    case Delete = 'products.delete';
    case Restore = 'products.restore';
    case ForceDelete = 'products.force-delete';
}
```

### Exemplo Real: UserPermissions

```php
// app/Enums/Permissions/UserPermissions.php
enum UserPermissions: string
{
    case All = 'users';
    case View = 'users.view';
    case Create = 'users.create';
    case Update = 'users.update';
    case Delete = 'users.delete';
    case Restore = 'users.restore';
    case ForceDelete = 'users.force-delete';
}
```

## 🔐 Padrão de Nomenclatura

### Convenções

1. **All**: Permissão geral do módulo (`products`)
2. **View**: Visualizar registros (`products.view`)
3. **Create**: Criar registros (`products.create`)
4. **Update**: Atualizar registros (`products.update`)
5. **Delete**: Excluir registros (`products.delete`)
6. **Restore**: Restaurar registros (`products.restore`)
7. **ForceDelete**: Excluir permanentemente (`products.force-delete`)

### Permissões Especiais

Para permissões de sistema:

```php
enum SystemPermissions: string
{
    case SystemSettingsManage = 'system.settings.manage';
    case LogViewerAccess = 'system.log-viewer.access';
}
```

## 🎯 Usando Permissões

### Em Policies

```php
// app/Policies/ProductPolicy.php
use App\Enums\Permissions\ProductPermissions;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(ProductPermissions::All);
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can(ProductPermissions::View);
    }

    public function create(User $user): bool
    {
        return $user->can(ProductPermissions::Create);
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can(ProductPermissions::Update);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can(ProductPermissions::Delete);
    }
}
```

### Em Resources

```php
// ProductResource.php
public static function canViewAny(): bool
{
    return auth()->user()?->can(ProductPermissions::All) ?? false;
}
```

### Em Páginas

```php
// ManageSystem.php
public static function canAccess(): bool
{
    return auth()->user()?->can(SystemPermissions::SystemSettingsManage) ?? false;
}
```

### Em Navigation Items

```php
// AdminPanelProvider.php
NavigationItem::make('Log Viewer')
    ->visible(fn () => Auth::user()?->can(SystemPermissions::LogViewerAccess))
```

### Em Blade Templates

```blade
@can(ProductPermissions::Create)
    <a href="{{ route('products.create') }}">Criar Produto</a>
@endcan
```

## 👥 Roles e Permissões

### Criando Roles

```php
use Spatie\Permission\Models\Role;

$role = Role::create(['name' => 'admin']);
$role->givePermissionTo([
    ProductPermissions::All,
    UserPermissions::All,
]);
```

### Atribuindo Roles a Usuários

```php
$user->assignRole('admin');
// ou
$user->assignRole(Roles::Admin);
```

### Verificando Roles

```php
$user->hasRole('admin');
$user->hasAnyRole(['admin', 'editor']);
$user->hasAllRoles(['admin', 'editor']);
```

## 📋 Seeders de Permissões

### Criando um Seeder

```php
<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permissions\ProductPermissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ProductPermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ProductPermissions::cases() as $permission) {
            Permission::firstOrCreate([
                'name' => $permission->value,
                'guard_name' => 'web',
            ]);
        }
    }
}
```

### Exemplo Real: RoleSeeder

```php
// database/seeders/RoleSeeder.php
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::firstOrCreate(['name' => Roles::Admin->value]);
        
        $admin->givePermissionTo([
            UserPermissions::All,
            RolePermissions::All,
            PermissionPermissions::All,
            SystemPermissions::SystemSettingsManage,
        ]);
    }
}
```

## 🔄 Enums de Roles

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum Roles: string
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Viewer = 'viewer';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Editor => 'Editor',
            self::Viewer => 'Visualizador',
        };
    }
}
```

## 🎯 Verificação de Acesso ao Panel

O modelo `User` implementa `FilamentUser` e verifica acesso ao panel:

```php
// app/Models/User.php
public function canAccessPanel(?Panel $panel): bool
{
    $panelId = $panel?->getId();

    return $this->can("system.panels.view.{$panelId}");
}
```

## 📊 Estrutura de Permissões do Sistema

### Permissões de Painel

- `system.panels.view.admin`: Acesso ao painel admin

### Permissões de Usuários

- `users`: Todas as permissões de usuários
- `users.view`: Visualizar usuários
- `users.create`: Criar usuários
- `users.update`: Atualizar usuários
- `users.delete`: Excluir usuários

### Permissões de Sistema

- `system.settings.manage`: Gerenciar configurações do sistema
- `system.logs.view`: Visualizar logs
- `system.activity.view`: Visualizar activity log

## 🎯 Boas Práticas

1. **Use Enums**: Sempre crie Enums para permissões
2. **Nomenclatura Consistente**: Siga o padrão `{module}.{action}`
3. **Policies**: Sempre crie Policies para Resources
4. **Seeders**: Crie seeders para popular permissões
5. **Verificação**: Sempre verifique permissões antes de ações críticas
6. **Documentação**: Documente permissões especiais

## 🔗 Próximos Passos

- [Policies e Autorização](08-policies-e-autorizacao.md) - Entenda como criar Policies
- [Enums e Convenções](09-enums-e-convencoes.md) - Veja outros tipos de Enums
- [Criando Recursos Filament](02-criando-recursos-filament.md) - Adicione permissões a Resources

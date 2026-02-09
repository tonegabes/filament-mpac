# Enums e Convenções

Este documento explica como usar Enums no projeto e as convenções estabelecidas.

## 📚 O que são Enums?

Enums são tipos de dados que permitem definir um conjunto fixo de valores nomeados. No projeto, usamos Enums para:
- Grupos de navegação
- Permissões
- Roles
- Layouts de página
- Outras configurações

## 🏗️ Estrutura de Enums

```
app/Enums/
├── NavGroups.php
├── Roles.php
├── PageLayouts.php
└── Permissions/
    ├── UserPermissions.php
    ├── RolePermissions.php
    ├── PermissionPermissions.php
    └── SystemPermissions.php
```

## 🧭 NavGroups (Grupos de Navegação)

### Estrutura

```php
<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use ToneGabes\Filament\Icons\Enums\Phosphor;

enum NavGroups: string implements HasIcon, HasLabel
{
    case Authorization = 'Autorização';
    case Tools = 'Ferramentas';
    case Settings = 'Configurações';
    case Files = 'Arquivos';

    public function getLabel(): string
    {
        return $this->value;
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Authorization => (string) Phosphor::ShieldCheck->getLabel(),
            self::Tools         => (string) Phosphor::Wrench->getLabel(),
            self::Settings      => (string) Phosphor::Gear->getLabel(),
            self::Files         => (string) Phosphor::File->getLabel(),
        };
    }
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

### Uso em Páginas

```php
// ManageSystem.php
public static function getNavigationGroup(): string
{
    return NavGroups::Settings->value;
}
```

## 🔐 Permissions Enums

### Estrutura

```php
<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

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

### Uso

```php
// Em Policies
return $user->can(UserPermissions::All);

// Em Resources
public static function canViewAny(): bool
{
    return auth()->user()?->can(UserPermissions::All) ?? false;
}
```

Veja [Sistema de Permissões](07-sistema-permissoes.md) para mais detalhes.

## 👥 Roles Enum

### Estrutura

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

### Uso

```php
$user->assignRole(Roles::Admin);
$user->hasRole(Roles::Admin);
```

## 🎨 PageLayouts Enum

### Estrutura

```php
<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use ToneGabes\Filament\Icons\Enums\Phosphor;

enum PageLayouts: string implements HasLabel, HasDescription, HasIcon
{
    case Split = 'split';
    case Centered = 'centered';
    case FullPage = 'fullpage';

    public function getLabel(): string
    {
        return match ($this) {
            self::Split => 'Dividido',
            self::Centered => 'Centralizado',
            self::FullPage => 'Página Completa',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::Split => 'Layout dividido com imagem e formulário',
            self::Centered => 'Layout centralizado',
            self::FullPage => 'Layout de página completa',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Split => (string) Phosphor::Columns->getLabel(),
            self::Centered => (string) Phosphor::Circle->getLabel(),
            self::FullPage => (string) Phosphor::Square->getLabel(),
        };
    }
}
```

### Uso

```php
// Em Settings
RadioList::make('auth_page_layout')
    ->label('Layout da página de login')
    ->options([
        PageLayouts::Split->value => PageLayouts::Split->getLabel(),
        PageLayouts::Centered->value => PageLayouts::Centered->getLabel(),
        PageLayouts::FullPage->value => PageLayouts::FullPage->getLabel(),
    ])
    ->descriptions([
        PageLayouts::Split->value => PageLayouts::Split->getDescription(),
        PageLayouts::Centered->value => PageLayouts::Centered->getDescription(),
        PageLayouts::FullPage->value => PageLayouts::FullPage->getDescription(),
    ])
    ->icons([
        PageLayouts::Split->value => PageLayouts::Split->getIcon(),
        PageLayouts::Centered->value => PageLayouts::Centered->getIcon(),
        PageLayouts::FullPage->value => PageLayouts::FullPage->getIcon(),
    ]);
```

## 🎯 Criando um Novo Enum

### Passo 1: Criar o Enum

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Ativo',
            self::Inactive => 'Inativo',
            self::Pending => 'Pendente',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Inactive => 'danger',
            self::Pending => 'warning',
        };
    }
}
```

### Passo 2: Usar no Model

```php
// Product.php
protected function casts(): array
{
    return [
        'status' => Status::class,
    ];
}
```

### Passo 3: Usar em Formulários

```php
// ProductForm.php
Select::make('status')
    ->label('Status')
    ->options(collect(Status::cases())->mapWithKeys(fn ($status) => [
        $status->value => $status->getLabel()
    ]))
    ->required();
```

## 🔧 Traits para Enums

### BetterEnum Trait

O projeto possui um trait `BetterEnum` que adiciona funcionalidades úteis:

```php
// app/Traits/BetterEnum.php
trait BetterEnum
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return array_map(
            fn ($case) => $case->getLabel(),
            self::cases()
        );
    }
}
```

### Uso

```php
enum Status: string
{
    use BetterEnum;

    case Active = 'active';
    case Inactive = 'inactive';
}
```

## 📋 Convenções

### Nomenclatura

- **PascalCase** para nomes de Enums: `NavGroups`, `UserPermissions`
- **PascalCase** para cases: `Authorization`, `All`, `View`
- **snake_case** para valores: `'users.view'`, `'authorization'`

### Organização

- Enums relacionados agrupados em pastas: `Permissions/`
- Um Enum por arquivo
- Namespace reflete a estrutura de pastas

### Implementações

- Use interfaces do Filament quando apropriado: `HasLabel`, `HasIcon`, `HasDescription`
- Implemente métodos úteis: `getLabel()`, `getIcon()`, `getColor()`

## 🎯 Boas Práticas

1. **Use Enums**: Prefira Enums a strings mágicas
2. **Type Safety**: Use type hints com Enums
3. **Interfaces**: Implemente interfaces do Filament quando útil
4. **Métodos Úteis**: Adicione métodos como `getLabel()`, `getColor()`
5. **Organização**: Agrupe Enums relacionados em pastas
6. **Documentação**: Documente Enums complexos

## 🔗 Próximos Passos

- [Sistema de Permissões](07-sistema-permissoes.md) - Veja Enums de permissões
- [Traits](10-traits.md) - Entenda traits como BetterEnum
- [Criando Recursos Filament](02-criando-recursos-filament.md) - Use Enums em Resources

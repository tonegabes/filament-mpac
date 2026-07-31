# Traits

Este documento explica os Traits disponíveis no projeto e como usá-los.

## 📚 Traits disponíveis

```text
app/Traits/
├── HasIsActiveScope.php
├── HasNotifications.php
└── BetterEnum.php
```

## ✅ HasIsActiveScope

Trait para modelos com coluna booleana `is_active`. Usado por `User`.

API atual (Laravel `#[Scope]`):

| Método | Tipo | Uso |
| --- | --- | --- |
| `active()` | query scope | `User::query()->active()->get()` |
| `activeCount()` | query scope | `User::query()->activeCount()` |
| `isActive()` | instância | `$user->isActive()` |
| `isInactive()` | instância | `$user->isInactive()` |
| `activate()` | instância | `$user->activate()` |
| `deactivate()` | instância | `$user->deactivate()` |
| `toggleActive()` | instância | `$user->toggleActive()` |

### Estrutura

```php
<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @mixin Model
 *
 * @property bool $is_active
 */
trait HasIsActiveScope
{
    /**
     * @param  Builder<covariant Model>  $query
     * @return Builder<covariant Model>
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        $query->where('is_active', true);

        if (DB::connection()->getDriverName() === 'mysql') {
            $query->useIndex('idx_is_active');
        }

        return $query;
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function isInactive(): bool
    {
        return ! $this->isActive();
    }

    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    public function toggleActive(): bool
    {
        return $this->update(['is_active' => ! $this->is_active]);
    }

    /**
     * @param  Builder<covariant Model>  $query
     */
    #[Scope]
    protected function activeCount(Builder $query): int
    {
        return $query->where('is_active', true)->count();
    }
}
```

### Uso em Models

```php
use App\Traits\HasIsActiveScope;

class User extends Authenticatable
{
    use HasIsActiveScope;
}
```

### Exemplos

```php
// Filtrar ativos (scope de query)
$activeUsers = User::query()->active()->get();

// Contar ativos
$count = User::query()->activeCount();

// Estado e mutações na instância
$user->isActive();
$user->isInactive();
$user->activate();
$user->deactivate();
$user->toggleActive();
```

### Armadilha comum

Não confunda:

- `User::query()->active()` — scope de query
- `$user->isActive()` — método de instância

O nome antigo `HasActiveScope` / `scopeIsActive()` / `Product::isActive()` **não existe mais**.

## 🔔 HasNotifications

Trait para gerenciar notificações relacionadas ao modelo.

```php
use App\Traits\HasNotifications;

class User extends Model
{
    use HasNotifications;
}
```

## 🎯 BetterEnum

Utilitários para enums (`values()`, `labels()`, etc.).

```php
enum Status: string
{
    use BetterEnum;

    case Active = 'active';
    case Inactive = 'inactive';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Ativo',
            self::Inactive => 'Inativo',
        };
    }
}

Status::values(); // ['active', 'inactive']
Status::labels(); // ['Ativo', 'Inativo']
```

## 🔧 Criando um Trait

1. Coloque em `app/Traits/`
2. Prefira nomes `Has*` / `Can*`
3. Use type hints e PHPDoc
4. Para scopes Eloquent modernos, use `#[Scope]` em métodos `protected`
5. Use `boot{TraitName}` quando precisar de hooks de modelo

## 🔗 Próximos Passos

- [Modelos e Relacionamentos](14-modelos-e-relacionamentos.md)
- [Enums e Convenções](09-enums-e-convencoes.md)
- [Setup e Troubleshooting](17-setup-dependencias-e-troubleshooting.md)

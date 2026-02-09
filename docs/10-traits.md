# Traits

Este documento explica os Traits disponíveis no projeto e como criar e usar traits customizadas.

## 📚 O que são Traits?

Traits são mecanismos de reutilização de código em PHP. Permitem que classes compartilhem métodos sem herança múltipla. No projeto, temos vários traits úteis.

## 🏗️ Traits Disponíveis

```
app/Traits/
├── HasActiveScope.php
├── HasNotifications.php
└── BetterEnum.php
```

## ✅ HasActiveScope

O trait `HasActiveScope` adiciona funcionalidades relacionadas ao campo `is_active` em modelos.

### Estrutura

```php
<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasActiveScope
{
    /**
     * Scope para filtrar apenas registros ativos.
     *
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function scopeIsActive(Builder $query): Builder
    {
        $query->where('is_active', true);

        if (DB::connection()->getDriverName() === 'mysql') {
            $query->useIndex('idx_is_active');
        }

        return $query;
    }

    /**
     * Ativa o modelo.
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Desativa o modelo.
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Verifica se o modelo está ativo.
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Verifica se o modelo está inativo.
     */
    public function isInactive(): bool
    {
        return ! $this->isActive();
    }

    /**
     * Alterna o estado ativo do modelo.
     */
    public function toggleActive(): bool
    {
        return $this->update(['is_active' => ! $this->isActive()]);
    }

    /**
     * Conta o número de modelos ativos.
     *
     * @param  Builder<Model>  $query
     */
    public function scopeActiveCount(Builder $query): int
    {
        return $query->where('is_active', true)->count();
    }
}
```

### Uso em Models

```php
// Product.php
use App\Traits\HasActiveScope;

class Product extends Model
{
    use HasActiveScope;

    // ...
}
```

### Exemplos de Uso

```php
// Filtrar apenas ativos
$activeProducts = Product::isActive()->get();

// Ativar um produto
$product->activate();

// Desativar um produto
$product->deactivate();

// Verificar se está ativo
if ($product->isActive()) {
    // ...
}

// Alternar estado
$product->toggleActive();

// Contar ativos
$count = Product::activeCount();
```

## 🔔 HasNotifications

Trait para gerenciar notificações relacionadas ao modelo.

### Uso

```php
use App\Traits\HasNotifications;

class User extends Model
{
    use HasNotifications;
    
    // Métodos de notificação disponíveis
}
```

## 🎯 BetterEnum

Trait que adiciona funcionalidades úteis a Enums.

### Estrutura

```php
trait BetterEnum
{
    /**
     * Retorna array de valores do Enum.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Retorna array de labels do Enum.
     */
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

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Ativo',
            self::Inactive => 'Inativo',
        };
    }
}

// Uso
Status::values();  // ['active', 'inactive']
Status::labels();  // ['Ativo', 'Inativo']
```

## 🔧 Criando um Trait Customizado

### Exemplo: HasSlug

```php
<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Boot do trait.
     */
    public static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('name') && empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    /**
     * Scope para buscar por slug.
     */
    public function scopeWhereSlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }
}
```

### Uso

```php
class Product extends Model
{
    use HasSlug;

    protected $fillable = ['name', 'slug'];
}
```

## 🎯 Boas Práticas

1. **Organização**: Coloque traits em `app/Traits/`
2. **Nomenclatura**: Use nomes descritivos começando com verbo (`Has`, `Can`, etc.)
3. **Documentação**: Documente métodos públicos
4. **Type Hints**: Use type hints explícitos
5. **Boot Methods**: Use `boot{TraitName}` para inicialização
6. **Scopes**: Adicione scopes úteis quando apropriado

## 🔗 Próximos Passos

- [Modelos e Relacionamentos](14-modelos-e-relacionamentos.md) - Veja como usar traits em models
- [Enums e Convenções](09-enums-e-convencoes.md) - Veja BetterEnum em ação

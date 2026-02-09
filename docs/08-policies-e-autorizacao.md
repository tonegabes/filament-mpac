# Policies e Autorização

Este documento explica como criar e usar Policies no Laravel/Filament para controlar acesso a recursos.

## 📚 O que são Policies?

Policies são classes que organizam a lógica de autorização para um modelo ou recurso específico. Elas determinam quais ações um usuário pode realizar.

## 🏗️ Estrutura de Policies

```
app/Policies/
├── UserPolicy.php
├── RolePolicy.php
├── PermissionPolicy.php
└── ProductPolicy.php
```

## 📝 Criando uma Policy

### Usando Artisan

```bash
php artisan make:policy ProductPolicy --model=Product --no-interaction
```

### Estrutura Básica

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permissions\ProductPermissions;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(ProductPermissions::All);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        return $user->can(ProductPermissions::View);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(ProductPermissions::Create);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->can(ProductPermissions::Update);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->can(ProductPermissions::Delete);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        return $user->can(ProductPermissions::Restore);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return $user->can(ProductPermissions::ForceDelete);
    }
}
```

### Exemplo Real: UserPolicy

```php
// app/Policies/UserPolicy.php
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(UserPermissions::All);
    }

    public function view(User $user, User $model): bool
    {
        return $user->can(UserPermissions::View);
    }

    public function create(User $user): bool
    {
        return $user->can(UserPermissions::Create);
    }

    public function update(User $user, User $model): bool
    {
        return $user->can(UserPermissions::Update);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can(UserPermissions::Delete);
    }

    public function restore(User $user, User $model): bool
    {
        return $user->can(UserPermissions::Restore);
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->can(UserPermissions::ForceDelete);
    }
}
```

## 🔗 Registrando Policies

### Auto-descoberta (Padrão)

Laravel descobre automaticamente Policies seguindo a convenção:
- Model: `App\Models\Product`
- Policy: `App\Policies\ProductPolicy`

### Registro Manual

Se necessário, registre em `AuthServiceProvider`:

```php
// app/Providers/AuthServiceProvider.php
protected $policies = [
    Product::class => ProductPolicy::class,
];
```

## 🎯 Usando Policies em Resources

### Filament Auto-detecta Policies

O Filament detecta automaticamente Policies seguindo a convenção. Basta criar a Policy e o Filament a usará.

### Verificação Manual

```php
// ProductResource.php
public static function canViewAny(): bool
{
    return auth()->user()?->can('viewAny', Product::class) ?? false;
}
```

### Em Páginas do Resource

```php
// ListProducts.php
public function mount(): void
{
    abort_unless(
        auth()->user()?->can('viewAny', Product::class),
        403
    );
}
```

## 🔐 Métodos de Autorização

### Usando Gate

```php
if (Gate::allows('view', $product)) {
    // Usuário pode visualizar
}

if (Gate::denies('update', $product)) {
    // Usuário não pode atualizar
}
```

### Usando Model

```php
if ($user->can('view', $product)) {
    // Usuário pode visualizar
}

if ($user->cannot('update', $product)) {
    // Usuário não pode atualizar
}
```

### Em Blade

```blade
@can('view', $product)
    <a href="{{ route('products.show', $product) }}">Ver</a>
@endcan

@can('update', $product)
    <a href="{{ route('products.edit', $product) }}">Editar</a>
@endcan
```

## 🎨 Autorização Condicional

### Baseada em Propriedades do Model

```php
public function update(User $user, Product $product): bool
{
    // Usuário só pode atualizar produtos próprios
    return $user->can(ProductPermissions::Update) 
        && $product->user_id === $user->id;
}
```

### Baseada em Relacionamentos

```php
public function delete(User $user, Product $product): bool
{
    // Usuário só pode excluir se for o dono ou admin
    return $user->can(ProductPermissions::Delete) 
        && ($product->user_id === $user->id || $user->hasRole('admin'));
}
```

## 📋 Métodos Comuns de Policy

### viewAny

Determina se o usuário pode visualizar qualquer registro:

```php
public function viewAny(User $user): bool
{
    return $user->can(ProductPermissions::All);
}
```

### view

Determina se o usuário pode visualizar um registro específico:

```php
public function view(User $user, Product $product): bool
{
    return $user->can(ProductPermissions::View);
}
```

### create

Determina se o usuário pode criar novos registros:

```php
public function create(User $user): bool
{
    return $user->can(ProductPermissions::Create);
}
```

### update

Determina se o usuário pode atualizar um registro:

```php
public function update(User $user, Product $product): bool
{
    return $user->can(ProductPermissions::Update);
}
```

### delete

Determina se o usuário pode excluir um registro:

```php
public function delete(User $user, Product $product): bool
{
    return $user->can(ProductPermissions::Delete);
}
```

### restore

Determina se o usuário pode restaurar um registro excluído:

```php
public function restore(User $user, Product $product): bool
{
    return $user->can(ProductPermissions::Restore);
}
```

### forceDelete

Determina se o usuário pode excluir permanentemente:

```php
public function forceDelete(User $user, Product $product): bool
{
    return $user->can(ProductPermissions::ForceDelete);
}
```

## 🧪 Testando Policies

```php
// tests/Feature/Policies/ProductPolicyTest.php
it('allows admin to view any product', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect($admin->can('viewAny', Product::class))->toBeTrue();
});

it('denies user without permission to create product', function () {
    $user = User::factory()->create();

    expect($user->can('create', Product::class))->toBeFalse();
});
```

## 🎯 Boas Práticas

1. **Use Enums**: Sempre use Enums de permissões nas Policies
2. **Type Hints**: Use type hints explícitos
3. **Documentação**: Documente métodos com PHPDoc
4. **Testes**: Escreva testes para Policies
5. **Convenção**: Siga a convenção de nomenclatura do Laravel
6. **Lógica Complexa**: Mantenha lógica de autorização nas Policies, não nos Resources

## 🔗 Próximos Passos

- [Sistema de Permissões](07-sistema-permissoes.md) - Entenda o sistema de permissões
- [Testes](13-testes.md) - Aprenda a testar Policies
- [Criando Recursos Filament](02-criando-recursos-filament.md) - Adicione autorização a Resources

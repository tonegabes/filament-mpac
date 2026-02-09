# Testes

Este documento explica como escrever testes para recursos Filament usando Pest, seguindo as convenções do projeto.

## 📚 Visão Geral

O projeto usa **Pest** para testes. Todos os testes devem ser escritos usando a sintaxe do Pest e seguir os padrões estabelecidos.

## 🏗️ Estrutura de Testes

```
tests/
├── Feature/
│   ├── Filament/
│   │   ├── UserResourceTest.php
│   │   ├── RoleResourceTest.php
│   │   └── PermissionResourceTest.php
│   └── Policies/
│       ├── UserPolicyTest.php
│       └── RolePolicyTest.php
└── Unit/
    └── Models/
        └── UserTest.php
```

## 🧪 Testando Resources Filament

### Setup Básico

```php
<?php

declare(strict_types=1);

use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Limpa cache de permissões
    app()->make(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    
    // Seed permissões e roles
    $this->seed(Database\Seeders\PermissionSeeder::class);
    $this->seed(Database\Seeders\RoleSeeder::class);

    // Cria usuário admin e autentica
    $admin = User::factory()->create();
    $admin->assignRole(Roles::Admin->value);

    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});
```

### Testando Listagem

```php
it('can render list products page and see records', function (): void {
    $products = Product::factory()->count(3)->create();

    Livewire::test(ListProducts::class)
        ->assertCanSeeTableRecords($products);
});
```

### Testando Busca

```php
it('can list and search products', function (): void {
    $product = Product::factory()->create(['name' => 'Produto Teste']);

    Livewire::test(ListProducts::class)
        ->searchTable('Produto')
        ->assertCanSeeTableRecords([$product])
        ->searchTable('inexistente')
        ->assertCanNotSeeTableRecords([$product]);
});
```

### Testando Criação

```php
it('can create a product', function (): void {
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Novo Produto',
            'price' => 99.99,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Product::class, [
        'name' => 'Novo Produto',
        'price' => 99.99,
        'is_active' => true,
    ]);
});
```

### Testando Edição

```php
it('can edit a product', function (): void {
    $product = Product::factory()->create(['name' => 'Nome Original']);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm([
            'name' => 'Nome Atualizado',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->refresh()->name)->toBe('Nome Atualizado');
});
```

### Testando Validação

```php
it('validates required fields when creating product', function (): void {
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => '', // Campo obrigatório vazio
        ])
        ->call('create')
        ->assertHasFormErrors(['name']);
});
```

## 🔐 Testando Policies

```php
<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows admin to view any product', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(Roles::Admin->value);

    expect($admin->can('viewAny', Product::class))->toBeTrue();
});

it('denies user without permission to create product', function (): void {
    $user = User::factory()->create();

    expect($user->can('create', Product::class))->toBeFalse();
});
```

## 📋 Assertions Comuns do Filament

### Tabelas

```php
// Ver registros na tabela
->assertCanSeeTableRecords($records)

// Não ver registros na tabela
->assertCanNotSeeTableRecords($records)

// Buscar na tabela
->searchTable('termo')

// Ordenar tabela
->sortTable('name', 'asc')
```

### Formulários

```php
// Preencher formulário
->fillForm(['field' => 'value'])

// Verificar erros
->assertHasFormErrors(['field'])

// Verificar sem erros
->assertHasNoFormErrors()

// Chamar ação
->call('create')
->call('save')
```

### Notificações

```php
// Verificar notificação
->assertNotified()

// Verificar redirecionamento
->assertRedirect()
```

## 🎯 Exemplo Completo: UserResourceTest

```php
<?php

declare(strict_types=1);

use App\Enums\Roles;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->make(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    $this->seed(Database\Seeders\PermissionSeeder::class);
    $this->seed(Database\Seeders\RoleSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole(Roles::Admin->value);

    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('can render list users page and see records', function (): void {
    $users = User::factory()->count(3)->create();

    Livewire::test(ListUsers::class)
        ->assertCanSeeTableRecords($users);
});

it('can list and search users', function (): void {
    $user = User::factory()->create(['name' => 'John Doe']);

    Livewire::test(ListUsers::class)
        ->searchTable('John')
        ->assertCanSeeTableRecords([$user])
        ->searchTable('unknown')
        ->assertCanNotSeeTableRecords([$user]);
});

it('can create a user', function (): void {
    $role = Role::first();

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'username' => 'newuser',
            'is_active' => true,
            'roles' => [$role->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(User::class, [
        'name' => 'New User',
        'email' => 'newuser@example.com',
    ]);
});

it('can edit a user', function (): void {
    $user = User::factory()->create(['name' => 'Original Name']);

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->refresh()->name)->toBe('Updated Name');
});
```

## 🏃 Executando Testes

### Todos os Testes

```bash
php artisan test
```

### Arquivo Específico

```bash
php artisan test tests/Feature/Filament/UserResourceTest.php
```

### Filtro por Nome

```bash
php artisan test --filter="can create a user"
```

## 🎯 Boas Práticas

1. **RefreshDatabase**: Sempre use `RefreshDatabase` para limpar banco entre testes
2. **Setup**: Configure usuário autenticado no `beforeEach`
3. **Factories**: Use factories para criar dados de teste
4. **Assertions Específicas**: Use assertions específicas do Filament
5. **Nomes Descritivos**: Use nomes de teste descritivos
6. **Um Teste, Uma Coisa**: Teste uma funcionalidade por vez
7. **Arrange-Act-Assert**: Siga o padrão AAA

## 🔗 Próximos Passos

- [Criando Recursos Filament](02-criando-recursos-filament.md) - Crie Resources testáveis
- [Policies e Autorização](08-policies-e-autorizacao.md) - Teste autorização

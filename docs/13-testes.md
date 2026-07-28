# Testes

Este documento descreve os padrões de testes atuais do projeto com Pest.

## 📚 Stack de testes

- Pest v5
- PHPUnit 13
- Plugin `pest-plugin-laravel`
- Testes de Feature e Unit

## 🏗️ Estrutura atual

```text
tests/
├── Feature/
│   ├── Filament/
│   │   ├── UserResourceTest.php
│   │   ├── RoleResourceTest.php
│   │   ├── PermissionResourceTest.php
│   │   ├── DocumentResourceTest.php
│   │   ├── ImageResourceTest.php
│   │   ├── MediaResourceTest.php
│   │   ├── ManageSystemTest.php
│   │   ├── LoginTest.php
│   │   └── RegisterTest.php
│   ├── Policies/
│   │   ├── UserPolicyTest.php
│   │   ├── RolePolicyTest.php
│   │   └── PermissionPolicyTest.php
│   └── Seeders/
│       ├── PermissionSeederTest.php
│       ├── RoleSeederTest.php
│       └── UserSeederTest.php
└── Unit/
    ├── Enums/
    ├── Models/
    ├── Services/Auth/
    ├── Settings/
    ├── Traits/
    ├── Support/
    └── Filament/Actions/
```

## 🧪 Padrão para testes Filament

Exemplo de setup típico:

```php
uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
    $this->seed(PermissionSeeder::class);
    $this->seed(RoleSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Developer->value);

    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});
```

## ✅ Assertions comuns

### Tabela

```php
Livewire::test(ListUsers::class)
    ->assertCanSeeTableRecords($users)
    ->searchTable('John')
    ->assertCanSeeTableRecords([$user]);
```

### Formulário

```php
Livewire::test(CreateUser::class)
    ->fillForm([
        'name' => 'Novo Usuário',
        'email' => 'novo@exemplo.com',
        'username' => 'novo@exemplo.com',
    ])
    ->call('create')
    ->assertHasNoFormErrors();
```

### Policy

```php
it('denies user without permission to create users', function (): void {
    $user = User::factory()->create();

    expect($user->can('create', User::class))->toBeFalse();
});
```

## ▶️ Execução recomendada

Para manter velocidade e foco, use o menor escopo possível com `--compact`.

### Arquivo específico

```bash
php artisan test --compact tests/Feature/Filament/UserResourceTest.php
```

### Filtro por nome

```bash
php artisan test --compact --filter="can create a user"
```

### Suite completa (quando necessário)

```bash
php artisan test --compact
```

## 🎯 Boas Práticas

1. Use `RefreshDatabase` para isolamento.
2. Limpe cache de permissões no `beforeEach`.
3. Faça seed de permissões/roles antes de asserts de acesso.
4. Configure o painel atual com `Filament::setCurrentPanel(...)`.
5. Teste fluxos reais do projeto (auth local/ldap, resources de arquivos, settings).
6. Prefira testes pequenos e específicos em vez de cenários gigantes.

## ⚠️ Observações do projeto

- Recursos de arquivos (`Document`, `Image`, `Media`) hoje têm foco em listagem/visualização.
- Não assuma factories de `Document`/`Image` se elas não existirem; use o padrão já adotado nos testes atuais.
- Para autenticação, há testes dedicados em `LoginTest` e `RegisterTest`.

## 🔗 Próximos Passos

- [Sistema de Permissões](07-sistema-permissoes.md)
- [Policies e Autorização](08-policies-e-autorizacao.md)

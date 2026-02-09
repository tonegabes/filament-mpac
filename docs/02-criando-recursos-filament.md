# Criando Recursos Filament

Este guia explica como criar um Resource completo no Filament seguindo as convenções do projeto.

## 📚 O que é um Resource?

Um Resource é uma classe estática que define uma interface CRUD completa para um modelo Eloquent. Ele gerencia automaticamente:
- Listagem de registros
- Criação de novos registros
- Edição de registros existentes
- Visualização de registros
- Exclusão de registros

## 🚀 Criando um Resource

### Passo 1: Criar o Resource usando Artisan

```bash
php artisan make:filament-resource Product --generate --no-interaction
```

Isso criará a estrutura básica. No entanto, vamos seguir a estrutura do projeto.

### Passo 2: Estrutura de Pastas

Crie a seguinte estrutura:

```
app/Filament/Resources/Products/
├── ProductResource.php
├── Pages/
│   ├── ListProducts.php
│   ├── CreateProduct.php
│   └── EditProduct.php
├── Schemas/
│   └── ProductForm.php
└── Tables/
    └── ProductsTable.php
```

### Passo 3: Classe Principal do Resource

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products;

use App\Enums\NavGroups;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $modelLabel = 'Produto';

    protected static ?string $pluralModelLabel = 'Produtos';

    protected static string|BackedEnum|null $navigationIcon = Phosphor::Package;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string
    {
        return NavGroups::Tools->value;
    }

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
```

### Propriedades Importantes

- `$model`: Classe do modelo Eloquent
- `$modelLabel`: Label singular (usado em "Criar Produto")
- `$pluralModelLabel`: Label plural (usado no menu)
- `$navigationIcon`: Ícone do Phosphor Icons
- `$recordTitleAttribute`: Atributo usado como título do registro

### Passo 4: Páginas do Resource

#### ListProducts.php

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;
}
```

#### CreateProduct.php

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}
```

#### EditProduct.php

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;
}
```

### Passo 5: Schema do Formulário

Veja [Schemas e Formulários](03-schemas-e-formularios.md) para detalhes completos.

### Passo 6: Configuração da Tabela

Veja [Tabelas](04-tabelas.md) para detalhes completos.

## 📋 Exemplo Completo: UserResource

Vamos analisar o `UserResource` existente como referência:

```php
// app/Filament/Resources/Users/UserResource.php
class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $modelLabel = 'Usuário';
    protected static string|BackedEnum|null $navigationIcon = Phosphor::Users;
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string
    {
        return NavGroups::Authorization->value;
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
```

## 🔐 Adicionando Autorização

Para adicionar autorização ao Resource, crie uma Policy:

```php
// app/Policies/ProductPolicy.php
class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(ProductPermissions::All);
    }
    
    // ... outros métodos
}
```

E registre no Resource:

```php
public static function getModel(): string
{
    return static::$model;
}
```

Veja [Policies e Autorização](08-policies-e-autorizacao.md) para mais detalhes.

## 📄 Página de Visualização (View)

Para adicionar uma página de visualização (somente leitura):

```php
// ProductResource.php
public static function getPages(): array
{
    return [
        'index' => ListProducts::route('/'),
        'create' => CreateProduct::route('/create'),
        'view' => ViewProduct::route('/{record}'),
        'edit' => EditProduct::route('/{record}/edit'),
    ];
}

// Pages/ViewProduct.php
class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    public static function infolist(Schema $schema): Schema
    {
        return ProductInfolist::configure($schema);
    }
}
```

## 🎯 Boas Práticas

1. **Sempre separe Schemas**: Use classes separadas para Form, Table e Infolist
2. **Use Enums para navegação**: Sempre use `NavGroups` para grupos de navegação
3. **Ícones Phosphor**: Sempre use `Phosphor` enum, nunca Hero Icons
4. **Type Hints**: Sempre use type hints explícitos
5. **Strict Types**: Sempre declare `strict_types=1`
6. **Labels em português**: Use labels descritivos em português

## 🔗 Próximos Passos

- [Schemas e Formulários](03-schemas-e-formularios.md) - Configure formulários
- [Tabelas](04-tabelas.md) - Configure tabelas
- [Sistema de Permissões](07-sistema-permissoes.md) - Adicione permissões

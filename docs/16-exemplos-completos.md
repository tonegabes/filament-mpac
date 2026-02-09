# Exemplos Completos

Este documento apresenta exemplos completos e práticos de como criar recursos seguindo todas as convenções do projeto.

## 📚 Índice de Exemplos

1. [Criar um Resource Completo](#criar-um-resource-completo)
2. [Criar uma Página de Configurações](#criar-uma-página-de-configurações)
3. [Criar um Componente Customizado](#criar-um-componente-customizado)

## 🚀 Criar um Resource Completo

Vamos criar um Resource completo para `Product` seguindo todas as convenções.

### Passo 1: Criar o Model

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use HasActiveScope;
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty();
    }
}
```

### Passo 2: Criar Enum de Permissões

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

### Passo 3: Criar Policy

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permissions\ProductPermissions;
use App\Models\Product;
use App\Models\User;

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

### Passo 4: Criar Resource

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

### Passo 5: Criar Pages

```php
// Pages/ListProducts.php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;
}

// Pages/CreateProduct.php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}

// Pages/EditProduct.php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;
}
```

### Passo 6: Criar Form Schema

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações Básicas')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        Select::make('category_id')
                            ->label('Categoria')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        TextInput::make('price')
                            ->label('Preço')
                            ->numeric()
                            ->prefix('R$')
                            ->required(),

                        ToggleButtons::make('is_active')
                            ->label('Ativo')
                            ->boolean()
                            ->inline()
                            ->required(),
                    ]),

                Section::make('Descrição')
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('description')
                            ->label('Descrição')
                            ->rows(4),
                    ]),
            ])->columns(2);
    }
}
```

### Passo 7: Criar Table Schema

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Categoria')
                    ->badge()
                    ->color('info'),

                TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filtros aqui
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginationPageOptions([25, 50, 100]);
    }
}
```

## ⚙️ Criar uma Página de Configurações

Vamos criar uma página de configurações para o app.

### Passo 1: Criar Settings Class

```php
<?php

declare(strict_types=1);

namespace App\Settings;

use App\Enums\Permissions\SystemPermissions;
use BackedEnum;
use Spatie\LaravelSettings\Settings;

class AppSettings extends Settings
{
    public string $app_name = 'Minha Aplicação';
    public bool $maintenance_mode = false;
    public ?string $contact_email = null;

    public static function group(): string
    {
        return 'app';
    }

    public static function getPermission(): BackedEnum
    {
        return SystemPermissions::SystemSettingsManage;
    }
}
```

### Passo 2: Criar SettingsPage

```php
<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Enums\NavGroups;
use App\Enums\Permissions\SystemPermissions;
use App\Settings\AppSettings;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ManageApp extends SettingsPage
{
    protected static string $settings = AppSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::Gear;

    protected static ?string $navigationLabel = 'Aplicação';

    protected ?string $heading = 'Configurações da Aplicação';

    public static function getNavigationGroup(): string
    {
        return NavGroups::Settings->value;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can(SystemPermissions::SystemSettingsManage) ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Geral')
                    ->schema([
                        TextInput::make('app_name')
                            ->label('Nome da Aplicação')
                            ->required()
                            ->maxLength(255),

                        Toggle::make('maintenance_mode')
                            ->label('Modo de Manutenção')
                            ->helperText('Quando ativado, apenas administradores podem acessar')
                            ->onIcon(Phosphor::Check)
                            ->offIcon(Phosphor::X),

                        TextInput::make('contact_email')
                            ->label('Email de Contato')
                            ->email(),
                    ]),
            ]);
    }
}
```

## 🎨 Criar um Componente Customizado

Vamos criar um componente `ColorPicker` customizado.

### Passo 1: Criar a Classe

```php
<?php

declare(strict_types=1);

namespace App\Filament\Components\Forms;

use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Field;

class ColorPicker extends Field
{
    use HasExtraInputAttributes;

    protected string $view = 'filament.components.forms.color-picker';

    /**
     * @var string[]
     */
    public array $colors = [
        '#FF0000', '#00FF00', '#0000FF',
        '#FFFF00', '#FF00FF', '#00FFFF',
    ];

    /**
     * @param string[] $colors
     */
    public function colors(array $colors): static
    {
        $this->colors = $colors;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getColors(): array
    {
        return $this->colors;
    }
}
```

### Passo 2: Criar a View

```blade
{{-- resources/views/filament/components/forms/color-picker.blade.php --}}
@php
    $id = $getId();
    $statePath = $getStatePath();
    $extraInputAttributeBag = $getExtraInputAttributeBag()->class(['opacity-0 absolute pointer-events-none']);
    $colors = $getColors();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
    class="fi-fo-color-picker"
>
    <div class="grid grid-cols-6 gap-2">
        @foreach ($colors as $color)
            @php
                $inputId = "{$id}-{$color}";
            @endphp

            <div>
                <input
                    id="{{ $inputId }}"
                    name="{{ $id }}"
                    type="radio"
                    value="{{ $color }}"
                    wire:model="{{ $statePath }}"
                    {{ $extraInputAttributeBag }}
                >
                <label
                    for="{{ $inputId }}"
                    class="block w-10 h-10 rounded cursor-pointer border-2"
                    style="background-color: {{ $color }};"
                ></label>
            </div>
        @endforeach
    </div>
</x-dynamic-component>
```

### Passo 3: Usar no Formulário

```php
use App\Filament\Components\Forms\ColorPicker;

ColorPicker::make('color')
    ->label('Cor')
    ->colors(['#FF0000', '#00FF00', '#0000FF'])
    ->required();
```

## 🎯 Fluxo Completo de Desenvolvimento

1. **Criar Model** com traits e relacionamentos
2. **Criar Enum de Permissões**
3. **Criar Policy** com métodos de autorização
4. **Criar Resource** com configuração básica
5. **Criar Pages** (List, Create, Edit)
6. **Criar Form Schema** separado
7. **Criar Table Schema** separado
8. **Criar Seeder** de permissões
9. **Escrever Testes** para o Resource
10. **Documentar** funcionalidades importantes

## 🔗 Referências

- [Criando Recursos Filament](02-criando-recursos-filament.md)
- [Schemas e Formulários](03-schemas-e-formularios.md)
- [Tabelas](04-tabelas.md)
- [Páginas Customizadas](05-paginas-customizadas.md)
- [Componentes Customizados](06-componentes-customizados.md)
- [Sistema de Permissões](07-sistema-permissoes.md)
- [Policies e Autorização](08-policies-e-autorizacao.md)
- [Settings](11-settings.md)
- [Testes](13-testes.md)

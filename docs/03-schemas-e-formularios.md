# Schemas e Formulários

Este documento explica como criar e estruturar formulários usando Schemas separados, seguindo as convenções do projeto.

## 📚 O que são Schemas?

Schemas são classes que definem a estrutura de formulários, tabelas e infolists de forma organizada e reutilizável. No projeto, seguimos o padrão de **separar Schemas em classes próprias**.

## 🏗️ Estrutura de Schemas

Para cada Resource, temos três tipos de Schemas:

1. **Form Schema**: Define campos do formulário (criar/editar)
2. **Table Schema**: Define colunas e configurações da tabela
3. **Infolist Schema**: Define campos de visualização (somente leitura)

```
app/Filament/Resources/{Entity}/Schemas/
├── {Entity}Form.php        # Formulário
├── {Entity}Table.php        # Tabela (ou em Tables/)
└── {Entity}Infolist.php     # Visualização
```

## 📝 Criando um Form Schema

### Estrutura Básica

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
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
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('price')
                            ->label('Preço')
                            ->numeric()
                            ->prefix('R$')
                            ->required(),
                    ]),
            ]);
    }
}
```

### Exemplo Real: UserForm

```php
// app/Filament/Resources/Users/Schemas/UserForm.php
class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados Pessoais')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->maxLength(255)
                            ->required(),

                        TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->required(),

                        TextInput::make('username')
                            ->maxLength(255)
                            ->required(),

                        ToggleButtons::make('is_active')
                            ->label('Ativo')
                            ->boolean()
                            ->inline()
                            ->required(),
                    ]),

                Section::make('Perfis')
                    ->columnSpanFull()
                    ->description('Selecione os perfis associados a esse usuário.')
                    ->schema([
                        CheckboxCards::make('roles')
                            ->hiddenLabel()
                            ->bulkToggleable()
                            ->columns(3)
                            ->relationship('roles', 'name')
                            ->required(),
                    ]),
            ])->columns(2);
    }
}
```

## 🧩 Componentes de Formulário Comuns

### TextInput

```php
TextInput::make('name')
    ->label('Nome')
    ->required()
    ->maxLength(255)
    ->placeholder('Digite o nome')
    ->helperText('Nome completo do produto');
```

### Select com Relacionamento

```php
Select::make('category_id')
    ->label('Categoria')
    ->relationship('category', 'name')
    ->required()
    ->searchable()
    ->preload();
```

### Toggle

```php
Toggle::make('is_active')
    ->label('Ativo')
    ->default(true)
    ->onIcon(Phosphor::Check)
    ->offIcon(Phosphor::X);
```

### FileUpload (Spatie Media Library)

```php
SpatieMediaLibraryFileUpload::make('image')
    ->label('Imagem')
    ->collection('images')
    ->image()
    ->imageEditor()
    ->required();
```

### CheckboxCards (Relacionamento Many-to-Many)

```php
CheckboxCards::make('tags')
    ->label('Tags')
    ->relationship('tags', 'name')
    ->bulkToggleable()
    ->columns(3);
```

## 📋 Organizando com Sections

Use `Section` para agrupar campos relacionados:

```php
Section::make('Informações Básicas')
    ->columns(2)                    // 2 colunas
    ->columnSpanFull()              // Ocupa todas as colunas
    ->collapsible()                 // Pode ser colapsado
    ->description('Preencha as informações básicas')
    ->schema([
        // Campos aqui
    ])
```

## 🔗 Relacionamentos

### BelongsTo (Select)

```php
Select::make('user_id')
    ->label('Usuário')
    ->relationship('user', 'name')
    ->required();
```

### BelongsToMany (CheckboxCards)

```php
CheckboxCards::make('roles')
    ->relationship('roles', 'name')
    ->columns(3)
    ->required();
```

### HasMany (Repeater)

```php
Repeater::make('items')
    ->relationship('items')
    ->schema([
        TextInput::make('name')->required(),
        TextInput::make('quantity')->numeric()->required(),
    ])
    ->columns(2);
```

## ✅ Validação

### Regras Básicas

```php
TextInput::make('email')
    ->email()                    // Validação de email
    ->required()                 // Obrigatório
    ->maxLength(255)            // Tamanho máximo
    ->unique(ignoreRecord: true) // Único (ignora registro atual)
```

### Validação Customizada

```php
TextInput::make('cpf')
    ->label('CPF')
    ->mask('999.999.999-99')
    ->validationAttribute('CPF')
    ->rules(['cpf']);
```

## 🎨 Layout com Grid e Sections

```php
return $schema
    ->columns(2)  // Layout geral em 2 colunas
    ->components([
        Section::make('Dados Pessoais')
            ->columns(2)  // 2 colunas dentro da section
            ->columnSpanFull()  // Section ocupa todas as colunas
            ->schema([
                TextInput::make('name')
                    ->columnSpan(1),  // Ocupa 1 coluna
                
                TextInput::make('email')
                    ->columnSpan(1),
                
                TextInput::make('address')
                    ->columnSpanFull(),  // Ocupa todas as colunas
            ]),
    ]);
```

## 🔄 Callbacks e Hooks

### afterStateUpdated

```php
SpatieMediaLibraryFileUpload::make('file')
    ->afterStateUpdated(function ($state, Set $set) {
        if ($state instanceof TemporaryUploadedFile) {
            $set('name', $state->getClientOriginalName());
        }
    });
```

### live() para Atualização em Tempo Real

```php
Select::make('category_id')
    ->live()
    ->afterStateUpdated(function (Set $set, $state) {
        // Atualiza outros campos quando categoria muda
        $set('price', Category::find($state)?->default_price);
    });
```

## 📄 Exemplo Completo: DocumentForm

```php
// app/Filament/Resources/Documents/Schemas/DocumentForm.php
class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('name')->default('Document Name Not Set'),

                SpatieMediaLibraryFileUpload::make('file')
                    ->label('Arquivo')
                    ->live()
                    ->required()
                    ->acceptedFileTypes(Document::getMimeTypeMap())
                    ->collection(Document::COLLECTION_NAME)
                    ->afterStateUpdated(function ($state, Set $set) {
                        if ($state instanceof TemporaryUploadedFile) {
                            $set('name', $state->getClientOriginalName());
                        }
                    }),
            ]);
    }
}
```

## 🎯 Boas Práticas

1. **Separe Schemas**: Sempre crie classes separadas para Form, Table e Infolist
2. **Use Sections**: Agrupe campos relacionados em Sections
3. **Labels em português**: Use labels descritivos
4. **Validação**: Sempre valide campos obrigatórios
5. **Relacionamentos**: Use `relationship()` quando possível
6. **Type Hints**: Sempre use type hints explícitos
7. **Helper Text**: Adicione textos de ajuda quando necessário

## 🔗 Próximos Passos

- [Tabelas](04-tabelas.md) - Configure tabelas
- [Componentes Customizados](06-componentes-customizados.md) - Crie componentes customizados
- [Modelos e Relacionamentos](14-modelos-e-relacionamentos.md) - Entenda relacionamentos Eloquent

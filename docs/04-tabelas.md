# Tabelas

Este documento explica como configurar tabelas no Filament, incluindo colunas, filtros, busca e ações.

## 📚 O que são Tables?

Tables são classes que definem a estrutura e comportamento de tabelas de listagem no Filament. No projeto, seguimos o padrão de **separar Table Schemas em classes próprias**.

## 🏗️ Estrutura de Tables

```
app/Filament/Resources/{Entity}/Tables/
└── {Entity}Table.php
```

Ou dentro de `Schemas/`:

```
app/Filament/Resources/{Entity}/Schemas/
└── {Entity}Table.php
```

## 📝 Criando uma Table Schema

### Estrutura Básica

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
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

                TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
```

### Exemplo Real: UsersTable

```php
// app/Filament/Resources/Users/Tables/UsersTable.php
class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),

                TextColumn::make('username')
                    ->label('Usuário')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('email')
                    ->searchable(),

                TextColumn::make('roles.name')
                    ->label('Perfis')
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->alignCenter()
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->paginationPageOptions([50, 100])
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
            ]);
    }
}
```

## 📊 Tipos de Colunas

### TextColumn

```php
TextColumn::make('name')
    ->label('Nome')
    ->searchable()              // Permite busca
    ->sortable()                // Permite ordenação
    ->limit(50)                 // Limita caracteres
    ->tooltip(fn ($record) => $record->name)  // Tooltip completo
    ->copyable()                // Permite copiar
    ->copyMessage('Copiado!')
    ->url(fn ($record) => route('products.show', $record))
    ->openUrlInNewTab();
```

### IconColumn

```php
IconColumn::make('is_active')
    ->label('Ativo')
    ->boolean()                 // Exibe check/x
    ->trueIcon(Phosphor::Check)
    ->falseIcon(Phosphor::X)
    ->trueColor('success')
    ->falseColor('danger');
```

### BadgeColumn (Relacionamentos)

```php
TextColumn::make('roles.name')
    ->label('Perfis')
    ->badge()                   // Exibe como badge
    ->color('info')            // Cor do badge
    ->separator(',');          // Separador entre múltiplos
```

### ImageColumn

```php
ImageColumn::make('avatar')
    ->label('Avatar')
    ->circular()
    ->defaultImageUrl(url('/images/default-avatar.png'));
```

### DateTimeColumn

```php
TextColumn::make('created_at')
    ->label('Criado em')
    ->dateTime('d/m/Y H:i')    // Formato customizado
    ->sortable()
    ->since()                  // "há 2 dias"
    ->toggleable();            // Pode ser ocultado
```

### MoneyColumn

```php
TextColumn::make('price')
    ->label('Preço')
    ->money('BRL')             // Formato monetário
    ->sortable();
```

## 🔍 Busca e Filtros

### Busca Global

```php
->searchable()  // Adiciona busca na coluna
```

### Filtros

```php
->filters([
    SelectFilter::make('status')
        ->options([
            'active' => 'Ativo',
            'inactive' => 'Inativo',
        ]),

    Filter::make('created_at')
        ->form([
            DatePicker::make('created_from')
                ->label('Criado a partir de'),
            DatePicker::make('created_until')
                ->label('Criado até'),
        ])
        ->query(function (Builder $query, array $data): Builder {
            return $query
                ->when(
                    $data['created_from'],
                    fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                )
                ->when(
                    $data['created_until'],
                    fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                );
        }),

    TernaryFilter::make('is_active')
        ->label('Status')
        ->placeholder('Todos')
        ->trueLabel('Apenas ativos')
        ->falseLabel('Apenas inativos'),
])
```

### Defer Filters (Padrão no Filament v4)

No Filament v4, os filtros são deferidos por padrão (usuário precisa clicar em "Aplicar"). Para desabilitar:

```php
->deferFilters(false)  // Aplica filtros automaticamente
```

## 🎯 Ações

### Record Actions (Ações por Registro)

```php
->recordActions([
    EditAction::make(),
    DeleteAction::make(),
    ViewAction::make(),
    
    Action::make('duplicate')
        ->label('Duplicar')
        ->icon(Phosphor::Copy)
        ->action(function ($record) {
            // Lógica de duplicação
        }),
])
```

### Bulk Actions (Ações em Massa)

```php
->toolbarActions([
    BulkActionGroup::make([
        DeleteBulkAction::make(),
        
        BulkAction::make('activate')
            ->label('Ativar selecionados')
            ->icon(Phosphor::Check)
            ->action(function ($records) {
                $records->each->activate();
            })
            ->requiresConfirmation(),
    ]),
])
```

## 📄 Paginação

```php
->paginationPageOptions([10, 25, 50, 100])  // Opções de itens por página
->defaultPaginationPageOption(25)          // Padrão
->paginationPosition(PaginationPosition::Both)  // Ambos os lados
```

## 🔄 Ordenação

```php
->defaultSort('created_at', 'desc')  // Ordenação padrão
->sortable()                         // Permite ordenação em todas as colunas
```

## 👁️ Colunas Toggleáveis

```php
TextColumn::make('username')
    ->toggleable(isToggledHiddenByDefault: true)  // Oculto por padrão
```

## 🎨 Agrupamento

```php
->groups([
    Group::make('status')
        ->label('Status')
        ->collapsible(),
])
```

## 📋 Exemplo Completo

```php
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
                SelectFilter::make('category_id')
                    ->label('Categoria')
                    ->relationship('category', 'name'),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Todos'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginationPageOptions([25, 50, 100]);
    }
}
```

## 🎯 Boas Práticas

1. **Separe Table Schema**: Crie classe separada para configuração da tabela
2. **Use Labels**: Sempre use labels descritivos em português
3. **Busca**: Adicione `searchable()` em colunas importantes
4. **Ordenação**: Adicione `sortable()` em colunas relevantes
5. **Toggleable**: Use para colunas menos importantes
6. **Filtros**: Adicione filtros úteis para o usuário
7. **Ações**: Forneça ações comuns (editar, excluir)

## 🔗 Próximos Passos

- [Schemas e Formulários](03-schemas-e-formularios.md) - Configure formulários
- [Actions Customizadas](12-actions-customizadas.md) - Crie ações customizadas
- [Sistema de Permissões](07-sistema-permissoes.md) - Adicione controle de acesso

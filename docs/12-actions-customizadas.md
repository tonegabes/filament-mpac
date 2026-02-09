# Actions Customizadas

Este documento explica como criar Actions customizadas no Filament para adicionar funcionalidades específicas.

## 📚 O que são Actions?

Actions são ações que podem ser executadas em Resources, Tables, Pages, etc. Elas encapsulam UI (modal, formulário) e lógica de execução.

## 🏗️ Estrutura

```
app/Filament/Actions/
└── CopyFileUrlAction.php
```

## 📝 Criando uma Action Customizada

### Estrutura Básica

```php
<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use Filament\Actions\Action;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class CustomAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'custom-action';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Ação Customizada')
            ->icon(Phosphor::Star)
            ->color('success')
            ->requiresConfirmation()
            ->action(function ($record) {
                // Lógica da ação
            });
    }
}
```

### Exemplo Real: CopyFileUrlAction

```php
// app/Filament/Actions/CopyFileUrlAction.php
class CopyFileUrlAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'copy-file-url';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('filament-actions::copy.link.label'))
            ->icon(Phosphor::Copy)
            ->keyBindings(['mod+c'])
            ->tooltip(fn (HasFileUrl $record): string => $record->getFileUrl())
            ->alpineClickHandler(function (HasFileUrl $record): string {
                $copyableState = Js::from($record->getFileUrl());
                $copyMessageJs = Js::from(__('filament-actions::copy.link.message'));

                return <<<JS
                    window.navigator.clipboard.writeText({$copyableState})
                    \$tooltip({$copyMessageJs}, {
                        theme: \$store.theme,
                        timeout: 2000,
                    })
                JS;
            });
    }
}
```

## 🎯 Usando Actions

### Em Resources

```php
// ProductResource.php
public static function getHeaderActions(): array
{
    return [
        Action::make('export')
            ->label('Exportar')
            ->icon(Phosphor::Download)
            ->action(function () {
                // Lógica de exportação
            }),
    ];
}
```

### Em Tables (Record Actions)

```php
// ProductsTable.php
->recordActions([
    Action::make('duplicate')
        ->label('Duplicar')
        ->icon(Phosphor::Copy)
        ->action(function ($record) {
            $newRecord = $record->replicate();
            $newRecord->save();
            
            Notification::make()
                ->title('Produto duplicado')
                ->success()
                ->send();
        }),
])
```

### Em Tables (Bulk Actions)

```php
// ProductsTable.php
->bulkActions([
    BulkActionGroup::make([
        BulkAction::make('activate')
            ->label('Ativar selecionados')
            ->icon(Phosphor::Check)
            ->action(function ($records) {
                $records->each->activate();
            })
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion(),
    ]),
])
```

## 🔧 Configurações Comuns

### Confirmação

```php
->requiresConfirmation()
->modalHeading('Confirmar ação')
->modalDescription('Tem certeza que deseja executar esta ação?')
->modalSubmitActionLabel('Confirmar')
```

### Formulário na Action

```php
->form([
    TextInput::make('reason')
        ->label('Motivo')
        ->required(),
])
->action(function (array $data, $record) {
    // Usa $data['reason']
})
```

### Notificações

```php
->action(function ($record) {
    // Lógica
    
    Notification::make()
        ->title('Ação executada')
        ->success()
        ->send();
})
```

### Redirecionamento

```php
->action(function ($record) {
    // Lógica
    
    return redirect()->route('products.index');
})
```

### Atalhos de Teclado

```php
->keyBindings(['mod+s'])  // Ctrl+S ou Cmd+S
->keyBindings(['shift+s'])
```

### Tooltip

```php
->tooltip('Clique para executar')
->tooltip(fn ($record) => "Editar {$record->name}")
```

## 🎨 Actions com Alpine.js

Para ações que precisam de JavaScript no cliente:

```php
->alpineClickHandler(function ($record): string {
    return <<<JS
        alert('Clique executado para: {$record->name}')
    JS;
})
```

## 📋 Exemplos Completos

### Action de Exportação

```php
Action::make('export')
    ->label('Exportar para CSV')
    ->icon(Phosphor::Download)
    ->color('success')
    ->action(function () {
        return response()->streamDownload(function () {
            // Gera CSV
        }, 'products.csv');
    })
```

### Action com Formulário

```php
Action::make('send_email')
    ->label('Enviar Email')
    ->icon(Phosphor::Envelope)
    ->form([
        TextInput::make('email')
            ->label('Email')
            ->email()
            ->required(),
        Textarea::make('message')
            ->label('Mensagem')
            ->required(),
    ])
    ->action(function (array $data, $record) {
        Mail::to($data['email'])->send(new ProductMail($record, $data['message']));
        
        Notification::make()
            ->title('Email enviado')
            ->success()
            ->send();
    })
```

## 🎯 Boas Práticas

1. **Nomes Descritivos**: Use nomes claros para actions
2. **Ícones**: Sempre use Phosphor Icons
3. **Confirmação**: Use `requiresConfirmation()` para ações destrutivas
4. **Notificações**: Informe o usuário sobre o resultado
5. **Validação**: Valide dados em actions com formulários
6. **Type Hints**: Use type hints explícitos

## 🔗 Próximos Passos

- [Tabelas](04-tabelas.md) - Use actions em tabelas
- [Páginas Customizadas](05-paginas-customizadas.md) - Use actions em páginas

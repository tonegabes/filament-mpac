# Componentes Customizados

Este documento explica como criar componentes Filament no projeto e o que existe hoje no código.

## 📚 Estado atual

Não há campos de formulário customizados em `app/Filament/Components/Forms/` (os antigos `IconPicker` e `ImagePicker` foram removidos).

O componente ativo no projeto é de navegação:

```text
app/Filament/Components/
└── Navigation/
    └── PanelSwitcher.php
```

### PanelSwitcher

Monta ações do menu do usuário para trocar entre painéis (`App\Enums\Panels`: `app` e `admin`), respeitando `User::canAccessPanel()`.

```php
use App\Filament\Components\Navigation\PanelSwitcher;

PanelSwitcher::userMenuItems();
```

Para campos ricos de formulário, preferir pacotes já integrados (ex.: `janczakb/filament-flex-fields`) em vez de recriar pickers locais.

## 🏗️ Estrutura de um Componente de Formulário

Se precisar criar um campo novo:

1. **Classe PHP**: lógica e propriedades
2. **View Blade**: renderização

```text
app/Filament/Components/Forms/
└── CustomField.php

resources/views/filament/components/forms/
└── custom-field.blade.php
```

### Passo 1: Classe

```php
<?php

declare(strict_types=1);

namespace App\Filament\Components\Forms;

use Filament\Forms\Components\Field;

class CustomField extends Field
{
    protected string $view = 'filament.components.forms.custom-field';

    public string $customProperty = '';

    public function customMethod(string $value): static
    {
        $this->customProperty = $value;

        return $this;
    }

    public function getCustomData(): string
    {
        return $this->customProperty;
    }
}
```

### Passo 2: View

```blade
@php
    $id = $getId();
    $statePath = $getStatePath();
    $extraInputAttributeBag = $getExtraInputAttributeBag();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div>
        <input
            type="text"
            id="{{ $id }}"
            wire:model="{{ $statePath }}"
            {{ $extraInputAttributeBag }}
        >
        <p>{{ $getCustomData() }}</p>
    </div>
</x-dynamic-component>
```

### Passo 3: Uso

```php
use App\Filament\Components\Forms\CustomField;

CustomField::make('field_name')
    ->label('Campo Customizado')
    ->customMethod('valor')
    ->required();
```

## 🎯 Traits Úteis do Filament

- `HasExtraInputAttributes` — atributos HTML extras no input
- `HasOptions` — listas de opções para selects/cards

## 🎯 Boas Práticas

1. Extenda `Filament\Forms\Components\Field` para campos de formulário.
2. Prefira Phosphor Icons (`ToneGabes\Filament\Icons\Enums\Phosphor`).
3. Não reintroduza pickers removidos sem necessidade clara; avalie flex-fields primeiro.
4. Documente métodos públicos com PHPDoc e escreva testes para lógica não trivial.
5. Mantenha a view Blade enxuta; lógica fica na classe PHP.

## 🔗 Próximos Passos

- [Schemas e Formulários](03-schemas-e-formularios.md)
- [Panel Provider](15-panel-provider.md)
- [Testes](13-testes.md)

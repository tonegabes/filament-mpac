# Componentes Customizados

Este documento explica como criar componentes de formulário customizados no Filament, seguindo as convenções do projeto.

## 📚 O que são Componentes Customizados?

Componentes customizados são campos de formulário criados especificamente para o projeto, que encapsulam lógica e UI reutilizáveis. No projeto, temos exemplos como `ImagePicker` e `IconPicker`.

## 🏗️ Estrutura de um Componente

Um componente customizado consiste em:

1. **Classe PHP**: Define a lógica e propriedades do componente
2. **View Blade**: Define a renderização HTML/UI

```
app/Filament/Components/Forms/
├── ImagePicker.php
└── IconPicker.php

resources/views/filament/components/forms/
├── image-picker.blade.php
└── icon-picker.blade.php
```

## 🖼️ Exemplo: ImagePicker

### Classe PHP

```php
<?php

declare(strict_types=1);

namespace App\Filament\Components\Forms;

use App\Models\Image;
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Concerns\HasOptions;
use Filament\Forms\Components\Field;
use Illuminate\Database\Eloquent\Collection;

class ImagePicker extends Field
{
    use HasExtraInputAttributes;
    use HasOptions;

    protected string $view = 'filament.components.forms.image-picker';

    public string $imageUrl = '#';

    public ?string $imageName = null;

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return Image::all();
    }
}
```

### View Blade

```blade
{{-- resources/views/filament/components/forms/image-picker.blade.php --}}
@php
    $id = $getId();
    $statePath = $getStatePath();
    $extraInputAttributeBag = $getExtraInputAttributeBag()->class(['opacity-0 absolute pointer-events-none']);
    $images = $getImages();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
    class="fi-fo-image-picker"
>
    @if ($images->isEmpty())
        <span class="fi-fo-image-picker__empty">Sem imagens na biblioteca.</span>
    @else
        <div class="fi-fo-image-picker__items">
            @foreach ($images as $image)
                @php
                    $inputId = "{$id}-{$image->name}";
                    $imageUrl = $image->getFileUrl();
                @endphp

                <div class="fi-fo-image-picker__item">
                    <input
                        id="{{ $inputId }}"
                        name="{{ $id }}"
                        type="radio"
                        value="{{ $image->getFilename() }}"
                        wire:model="{{ $statePath }}"
                        wire:loading.attr="disabled"
                        {{ $extraInputAttributeBag }}
                    >
                    <x-filament::button
                        :for="$inputId"
                        tag="label"
                        class="fi-fo-image-picker__button"
                    >
                        <img src="{{ $imageUrl }}" alt="">
                    </x-filament::button>
                </div>
            @endforeach
        </div>
    @endif
</x-dynamic-component>
```

## 🎨 Exemplo: IconPicker

### Classe PHP

```php
<?php

declare(strict_types=1);

namespace App\Filament\Components\Forms;

use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Concerns\HasOptions;
use Filament\Forms\Components\Field;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class IconPicker extends Field
{
    use HasExtraInputAttributes;
    use HasOptions;

    protected string $view = 'filament.components.forms.icon-picker';

    public string $icon = '';

    /**
     * @return Phosphor[]
     */
    public function getIcons(): array
    {
        return Phosphor::cases();
    }
}
```

### View Blade

```blade
{{-- resources/views/filament/components/forms/icon-picker.blade.php --}}
@php
    $id = $getId();
    $statePath = $getStatePath();
    $extraInputAttributeBag = $getExtraInputAttributeBag()->class(['opacity-0 absolute pointer-events-none']);
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
    class="fi-fo-toggle-buttons-wrp"
>
    <div class="grid grid-cols-6 gap-4">
        @foreach ($getIcons() as $icon)
            @php
                $inputId = "{$id}-{$icon->getLabel()}";
            @endphp

            <div class="fi-fo-toggle-buttons-btn-ctn w-full">
                <input
                    id="{{ $inputId }}"
                    name="{{ $id }}"
                    type="radio"
                    value="{{ $icon->getLabel() }}"
                    wire:model="{{ $statePath }}"
                    wire:loading.attr="disabled"
                    {{ $extraInputAttributeBag }}
                >
                <x-filament::button
                    :for="$inputId"
                    tag="label"
                    class="w-full justify-start"
                >
                    @svg($icon->getLabel(), ['class' => 'size-10'])
                    <span>{{ $icon->value }}</span>
                </x-filament::button>
            </div>
        @endforeach
    </div>
</x-dynamic-component>
```

## 🔧 Criando um Componente Customizado

### Passo 1: Criar a Classe

```php
<?php

declare(strict_types=1);

namespace App\Filament\Components\Forms;

use Filament\Forms\Components\Field;

class CustomField extends Field
{
    protected string $view = 'filament.components.forms.custom-field';

    // Propriedades customizadas
    public string $customProperty = '';

    // Métodos públicos para configuração
    public function customMethod(string $value): static
    {
        $this->customProperty = $value;

        return $this;
    }

    // Métodos para a view
    public function getCustomData(): string
    {
        return $this->customProperty;
    }
}
```

### Passo 2: Criar a View

```blade
{{-- resources/views/filament/components/forms/custom-field.blade.php --}}
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

### Passo 3: Usar no Formulário

```php
use App\Filament\Components\Forms\CustomField;

CustomField::make('field_name')
    ->label('Campo Customizado')
    ->customMethod('valor')
    ->required();
```

## 🎯 Traits Úteis

### HasExtraInputAttributes

Permite adicionar atributos HTML extras ao input:

```php
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;

class MyField extends Field
{
    use HasExtraInputAttributes;
    
    // Na view:
    // {{ $getExtraInputAttributeBag() }}
}
```

### HasOptions

Para componentes que precisam de opções:

```php
use Filament\Forms\Components\Concerns\HasOptions;

class MyField extends Field
{
    use HasOptions;
    
    public function options(array $options): static
    {
        $this->options = $options;
        return $this;
    }
}
```

## 🔄 Integração com Livewire

Componentes customizados funcionam automaticamente com Livewire através do `wire:model`:

```blade
<input
    wire:model="{{ $statePath }}"
    wire:loading.attr="disabled"
>
```

## 🎨 Estilização

Use classes Tailwind CSS para estilizar:

```blade
<div class="grid grid-cols-6 gap-4">
    <!-- Conteúdo -->
</div>
```

Para dark mode:

```blade
<div class="bg-white dark:bg-gray-800">
    <!-- Conteúdo -->
</div>
```

## 📋 Variáveis Disponíveis na View

- `$getId()`: ID único do campo
- `$getStatePath()`: Caminho do estado no Livewire
- `$getExtraInputAttributeBag()`: Atributos HTML extras
- `$field`: Instância do campo
- `$getFieldWrapperView()`: View do wrapper do campo

## 🎯 Boas Práticas

1. **Extenda Field**: Sempre estenda `Filament\Forms\Components\Field`
2. **Use Traits**: Aproveite traits como `HasExtraInputAttributes` e `HasOptions`
3. **View Separada**: Sempre crie view Blade separada
4. **Type Hints**: Use type hints explícitos
5. **Documentação**: Documente métodos públicos com PHPDoc
6. **Reutilização**: Crie componentes reutilizáveis
7. **Testes**: Escreva testes para componentes complexos

## 🔗 Próximos Passos

- [Schemas e Formulários](03-schemas-e-formularios.md) - Use componentes em formulários
- [Modelos e Relacionamentos](14-modelos-e-relacionamentos.md) - Entenda modelos usados nos componentes
- [Testes](13-testes.md) - Teste componentes customizados

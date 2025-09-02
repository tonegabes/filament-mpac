@php
    use Filament\Support\Enums\GridDirection;

    $fieldWrapperView = $getFieldWrapperView();
    $extraInputAttributeBag = $getExtraInputAttributeBag();
    $gridDirection = $getGridDirection() ?? GridDirection::Row;
    $id = $getId();
    $isDisabled = $isDisabled();
    $livewireKey = $getLivewireKey();
    $statePath = $getStatePath();
    $wireModelAttribute = $applyStateBindingModifiers('wire:model');

@endphp

<x-dynamic-component
    :component="$fieldWrapperView"
    :field="$field"
    class="fi-fo-radio-cards-wrp"
>
    <div
        {{
            $getExtraAttributeBag()
                ->grid($getColumns(), $gridDirection)
                ->class([
                    'fi-fo-radio-cards',
                ])
        }}
    >
        @foreach ($getOptions() as $value => $label)
            @php
                $inputAttributes = $extraInputAttributeBag
                    ->merge([
                        'disabled' => $isDisabled || $isOptionDisabled($value, $label),
                        'id' => $id . '-' . $value,
                        'name' => $id,
                        'value' => $value,
                        'wire:loading.attr' => 'disabled',
                        $wireModelAttribute => $statePath,
                    ], escape: false);
            @endphp

            <label
                class="fi-fo-radio-cards-label group/label"
                :class="{ 'fi-selected': $wire.{{ $statePath }} === '{{ $value }}' }"
                for="{{ $id . '-' . $value }}"
            >
                <div class="fi-fo-radio-cards-label-wrp">

                    @if ($hasIcon($value) && ! $isIconHidden())
                        @svg($getIcon($value), ['class' => 'fi-fo-radio-cards-label-icon'])
                    @endif

                    <div class="fi-fo-radio-cards-label-text">
                        <p>{{ $label }}</p>

                        @if ($hasDescription($value) && ! $isDescriptionHidden())
                            <p class="fi-fo-radio-cards-label-description">
                                {{ $getDescription($value) }}
                            </p>
                        @endif
                    </div>
                </div>

                <input
                    type="radio"
                    {{
                        $inputAttributes->class([
                            'fi-radio-input',
                            'fi-valid' => ! $errors->has($statePath),
                            'fi-invalid' => $errors->has($statePath),
                        ])
                    }}
                />
            </label>
        @endforeach
    </div>
</x-dynamic-component>

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
    class="fi-fo-radio-list-wrapper"
>
    <div
        {{
            $getExtraAttributeBag()
                ->grid($getColumns(), $gridDirection)
                ->class([
                    'fi-fo-radio-list',
                ])
        }}
    >
        @foreach ($getOptions() as $value => $label)
            @php
                $itemId = $id . '-' . $value;
                $inputAttributes = $extraInputAttributeBag
                    ->merge([
                        'disabled' => $isDisabled || $isOptionDisabled($value, $label),
                        'id' => $itemId,
                        'name' => $id,
                        'value' => $value,
                        'wire:loading.attr' => 'disabled',
                        $wireModelAttribute => $statePath,
                    ], escape: false);
            @endphp

            <label
                @class([
                    'fi-fo-radio-item group/radio-item',
                    'is-indicator-left' => $isInputIconLeft(),
                ])
                :class="{'is-selected': $wire.{{ $statePath }} === '{{ $value }}'}"
                for="{{ $itemId }}"
            >
                <div class="fi-fo-radio-item__content">

                    @if ($hasOptionIcon($value) && ! $isOptionIconHidden())
                        @svg($getOptionIcon($value), ['class' => 'fi-fo-radio-item__icon'])
                    @endif

                    <div class="fi-fo-radio-item__header">
                        <p class="fi-fo-radio-item__label">{{ $label }}</p>

                        @if ($hasDescription($value) && ! $isDescriptionHidden())
                            <p class="fi-fo-radio-item__description">
                                {{ $getDescription($value) }}
                            </p>
                        @endif
                    </div>
                </div>

                @if ($hasExtraText($value) && ! $isExtraTextHidden())
                    <p class="fi-fo-radio-item__extra">
                        {{ $getExtraText($value) }}
                    </p>
                @endif

                @if ($hasInputIcon() && ! $isInputIconHidden())
                    <template x-if="$wire.{{ $statePath }} === '{{ $value }}'">
                        <x-icon name="{{ $getSelectedInputIcon() }}"
                        @class([
                            'fi-fo-radio-item__indicator',
                                'is-indicator-partially-hidden' => $isInputIconSemiHidden(),
                            ])
                        />
                    </template>
                    <template x-if="$wire.{{ $statePath }} !== '{{ $value }}'">
                        <x-icon name="{{ $getDefaultInputIcon() }}"
                            @class([
                                'fi-fo-radio-item__indicator',
                                'is-indicator-partially-hidden' => $isInputIconSemiHidden(),
                            ])
                        />
                    </template>
                @endif

                <input
                    type="radio"
                    {{
                        $inputAttributes->class([
                            'hidden' => $hasInputIcon() || $isInputIconHidden(),
                            'fi-radio-input',
                            'is-indicator-partially-hidden' => $isInputIconSemiHidden(),
                            'fi-valid' => ! $errors->has($statePath),
                            'fi-invalid' => $errors->has($statePath),
                        ])
                    }}
                />
            </label>
        @endforeach
    </div>
</x-dynamic-component>

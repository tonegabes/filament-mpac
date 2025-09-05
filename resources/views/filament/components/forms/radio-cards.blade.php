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
    class="fi-fo-radio-cards-wrapper"
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
                    'fi-fo-radio-card group/radio-card',
                    'is-indicator-left' => $isIndicatorLeft(),
                    'is-centered' => $isItemsCenter(),
                ])
                :class="{'is-selected': $wire.{{ $statePath }} === '{{ $value }}'}"
                for="{{ $itemId }}"
            >

                @if ($hasOptionIcon($value) && ! $isOptionIconHidden())
                    @svg($getOptionIcon($value), ['class' => 'fi-fo-radio-card__icon'])
                @endif

                <div class="fi-fo-radio-card__content">
                    <div class="fi-fo-radio-card__header">
                        <p class="fi-fo-radio-card__label">{{ $label }}</p>

                        @if ($hasDescription($value) && ! $isDescriptionHidden())
                            <p class="fi-fo-radio-card__description">
                                {{ $getDescription($value) }}
                            </p>
                        @endif
                    </div>

                    @if ($hasExtraText($value) && ! $isExtraTextHidden())
                        <p class="fi-fo-radio-card__extra">
                            {{ $getExtraText($value) }}
                        </p>
                    @endif
                </div>

                @if ($hasIndicator() && ! $isIndicatorHidden())
                    <template x-if="$wire.{{ $statePath }} === '{{ $value }}'">
                        <x-icon
                            :name="$getSelectedIndicator()"
                            @class([
                                'fi-fo-radio-card__indicator',
                                'is-indicator-partially-hidden' => $isIndicatorPartiallyHidden(),
                            ])
                        />
                    </template>
                    <template x-if="$wire.{{ $statePath }} !== '{{ $value }}'">
                        <x-icon
                            :name="$getDefaultIndicator()"
                            @class([
                                'fi-fo-radio-card__indicator',
                                'is-indicator-partially-hidden' => $isIndicatorPartiallyHidden(),
                            ])
                        />
                    </template>
                @endif

                <input
                    type="radio"
                    {{
                        $inputAttributes->class([
                            'hidden' => $hasIndicator() || $isIndicatorHidden(),
                            'fi-radio-input',
                            'is-indicator-partially-hidden' => $isIndicatorPartiallyHidden(),
                            'fi-valid' => ! $errors->has($statePath),
                            'fi-invalid' => $errors->has($statePath),
                        ])
                    }}
                />
            </label>
        @endforeach
    </div>
</x-dynamic-component>

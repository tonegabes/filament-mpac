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
                $itemId = "$id-$value";
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
                class="fi-fo-radio-item group/radio-item"
                x-data="{ isSelected: false }"
                x-init="$watch(
                    '$wire.{{ $statePath }}',
                    value => isSelected = value === '{{ $value }}'
                )"
                :class="{ 'is-selected': isSelected }"
                :aria-checked="isSelected"
                :aria-selected="isSelected"
                for="{{ $itemId }}"
            >
                <div class="fi-fo-radio-item__content">

                    @if ($isIndicatorBefore() && $isIndicatorVisible())
                        <x-forms.radio-indicator
                            ::is-selected="isSelected"
                            :is-indicator-partially-hidden="$isIndicatorPartiallyHidden"
                            :default-indicator="$getIdleIndicator()"
                            :selected-indicator="$getSelectedIndicator()"
                        />
                    @endif

                    @if ($hasIconBefore() && $isIconVisible())
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

                @if ($isIndicatorAfter() && $isIndicatorVisible())
                    <x-forms.radio-indicator
                        ::is-selected="isSelected"
                        :is-indicator-partially-hidden="$isIndicatorPartiallyHidden"
                        :default-indicator="$getIdleIndicator()"
                        :selected-indicator="$getSelectedIndicator()"
                    />
                @endif

                <input
                    type="radio"
                    {{
                        $inputAttributes->class([
                            'hidden',
                            'fi-valid' => ! $errors->has($statePath),
                            'fi-invalid' => $errors->has($statePath),
                        ])
                    }}
                />
            </label>
        @endforeach
    </div>
</x-dynamic-component>

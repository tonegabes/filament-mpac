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
                    'fi-fo-radio-cards-label group/label',
                    'input-icon-left' => $isInputIconLeft(),
                    'items-center' => $isItemsCenter(),
                ])
                :class="{'fi-selected': $wire.{{ $statePath }} === '{{ $value }}'}"
                for="{{ $itemId }}"
            >

                @if ($hasOptionIcon($value) && ! $isOptionIconHidden())
                    @svg($getOptionIcon($value), ['class' => 'fi-radio-option-icon'])
                @endif

                <div class="fi-fo-radio-cards-label-wrp">
                    <div class="fi-fo-radio-cards-label-text">
                        <p>{{ $label }}</p>

                        @if ($hasDescription($value) && ! $isDescriptionHidden())
                            <p class="fi-fo-radio-cards-label-description">
                                {{ $getDescription($value) }}
                            </p>
                        @endif
                    </div>

                    @if ($hasExtraText($value) && ! $isExtraTextHidden())
                        <p class="fi-radio-extra-text">
                            {{ $getExtraText($value) }}
                        </p>
                    @endif
                </div>

                @if ($hasInputIcon() && ! $isInputIconHidden())
                    <template x-if="$wire.{{ $statePath }} === '{{ $value }}'">
                        <x-icon
                            :name="$getSelectedInputIcon()"
                            @class([
                                'fi-radio-input-icon',
                                'fi-radio-input-icon-semi-hidden' => $isInputIconSemiHidden(),
                            ])
                        />
                    </template>
                    <template x-if="$wire.{{ $statePath }} !== '{{ $value }}'">
                        <x-icon
                            :name="$getDefaultInputIcon()"
                            @class([
                                'fi-radio-input-icon',
                                'fi-radio-input-icon-semi-hidden' => $isInputIconSemiHidden(),
                            ])
                        />
                    </template>
                @endif

                <input
                    type="radio"
                    {{
                        $inputAttributes->class([
                            'hidden' => $hasInputIcon() || $isInputIconHidden(),
                            'fi-radio-input-icon-semi-hidden' => $isInputIconSemiHidden(),
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

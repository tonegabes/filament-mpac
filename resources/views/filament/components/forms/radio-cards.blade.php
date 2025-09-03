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
                class="fi-fo-radio-cards-label group/label"
                :class="{
                    'fi-selected': $wire.{{ $statePath }} === '{{ $value }}'
                }"
                for="{{ $itemId }}"
            >
                <div class="fi-fo-radio-cards-label-wrp">

                    @if ($hasLabelIcon($value) && ! $isLabelIconHidden())
                        @svg($getLabelIcon($value), ['class' => 'fi-fo-radio-cards-label-icon'])
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

                @if ($hasInputIcon() && ! $isInputIconHidden())
                    <x-icon name="{{ $getInputIcon() }}"
                        @class([
                            'fi-fo-radio-cards-input-icon',
                            'fi-fo-radio-cards-input-icon-semi-hidden' => $isIconSemiHidden(),
                        ])
                    />
                @endif

                <input
                    type="radio"
                    {{
                        $inputAttributes->class([
                            'hidden' => $hasInputIcon() || $isInputIconHidden(),
                            'fi-fo-radio-cards-input-icon-semi-hidden' => $isIconSemiHidden(),
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

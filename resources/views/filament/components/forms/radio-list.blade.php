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
    class="fi-fo-radio-list-wrp"
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
                class="fi-fo-radio-list-label group/label"
                :class="{
                    'fi-selected': $wire.{{ $statePath }} === '{{ $value }}'
                }"
                for="{{ $itemId }}"
            >
                <div class="fi-fo-radio-list-label-wrp">
                    <input
                        type="radio"
                        {{
                            $inputAttributes->class([
                                'hidden' => $hasInputIcon() || $isInputIconHidden(),
                                'fi-radio-input',
                                'fi-valid' => ! $errors->has($statePath),
                                'fi-invalid' => $errors->has($statePath),
                            ])
                        }}
                    />

                    @if ($hasInputIcon() && ! $isInputIconHidden())
                        <x-icon name="{{ $getInputIcon() }}"
                            @class([
                                'fi-fo-radio-list-input-icon',
                            ])
                        />
                    @endif

                    @if ($hasLabelIcon($value) && ! $isLabelIconHidden())
                        @svg($getLabelIcon($value), ['class' => 'fi-fo-radio-list-label-icon'])
                    @endif

                    <div class="fi-fo-radio-list-label-text">
                        <p>{{ $label }}</p>

                        @if ($hasDescription($value) && ! $isDescriptionHidden())
                            <p class="fi-fo-radio-list-label-description">
                                {{ $getDescription($value) }}
                            </p>
                        @endif
                    </div>
                </div>

                @if ($hasExtraText($value) && ! $isExtraTextHidden())
                    <p class="fi-fo-radio-list-label-extra-text">
                        {{ $getExtraText($value) }}
                    </p>
                @endif
            </label>
        @endforeach
    </div>
</x-dynamic-component>

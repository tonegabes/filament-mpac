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
                    $imageUrl = $image->getUrl();
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
                        class="fi-fo-image-picker__button "
                    >
                        <img src="{{ $imageUrl }}" alt="">
                    </x-filament::button>
                </div>
            @endforeach
        </div>
    @endif
</x-dynamic-component>

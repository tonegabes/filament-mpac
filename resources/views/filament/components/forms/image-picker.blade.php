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
        @foreach ($getImages() as $image)
            @php
                $inputId = "{$id}-{$image->name}";
                $imageUrl = $image->getUrl();
            @endphp

            <div class="fi-fo-toggle-buttons-btn-ctn w-full">
                <input
                    id="{{ $inputId }}"
                    name="{{ $id }}"
                    type="radio"
                    value="{{ $imageUrl }}"
                    wire:model="{{ $statePath }}"
                    wire:loading.attr="disabled"
                    {{ $extraInputAttributeBag }}
                >
                <x-filament::button
                    :for="$inputId"
                    tag="label"
                    class="w-full h-auto justify-start aspect-square"
                >
                    <img src="{{ $imageUrl }}" class="w-full h-auto object-cover" alt="">
                </x-filament::button>
            </div>
        @endforeach
    </div>
</x-dynamic-component>

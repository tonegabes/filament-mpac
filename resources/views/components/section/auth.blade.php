@props([
    'heading' => null,
    'subheading' => null,
])

@php
    $heading ??= $this->getHeading();
    $subheading ??= $this->getSubHeading();
@endphp

<section {{ $attributes->class(['fi-simple-page']) }}>
    {{
        \Filament\Support\Facades\FilamentView::renderHook(
            \Filament\View\PanelsRenderHook::SIMPLE_PAGE_START,
            scopes: $this->getRenderHookScopes()
        )
    }}

    <div class="fi-simple-page-content">
        <x-filament-panels::header.simple
            :heading="$heading"
            :logo="false"
            :subheading="$subheading"
        />

        {{ $slot }}
    </div>

    @if (! $this instanceof \Filament\Tables\Contracts\HasTable)
        <x-filament-actions::modals />
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_PAGE_END, scopes: $this->getRenderHookScopes()) }}
</section>

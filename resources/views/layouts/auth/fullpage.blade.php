@php
    use App\Settings\SystemSettings;

    $settings = app(SystemSettings::class);
@endphp

<x-filament-panels::layout.base>
    <div class="fi-simple-layout">

        <div class="flex items-center justify-between gap-4 py-6 px-8 w-full">
            <a href="/" class="flex items-center gap-2">

                <livewire:brand-logomark />
            </a>

            <nav>
                <a href="/"><x-phosphor-house-line class="size-6 text-black/50 dark:text-white/50 hover:text-primary" /></a>
            </nav>
        </div>

        <div class="flex flex-col lg:w-xl h-fit my-auto">
            <main class="fi-simple-main">
                {{ $slot }}
            </main>
        </div>

        <footer class="flex justify-between items-center text-sm w-full px-4 py-2 tracking-wide text-black/50 dark:text-white/50">
            <span>© {{ date('Y') }} - {{ $settings->getFooterBrandName() }}</span>
            <span>{{ config('app.version') }}</span>
        </footer>

    </div>
</x-filament-panels::layout.base>

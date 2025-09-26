@php
    use App\Settings\SystemSettings;

    $settings = app(SystemSettings::class);
@endphp

<x-filament-panels::layout.base>
    <div class="fi-simple-layout">

        <div class="flex items-center justify-between gap-4 py-6 px-8 w-full">
            <a href="/" class="flex items-center gap-2">
                @if ($settings->show_logo_in_topbar)
                    <img src="{{ $settings->getAppLogoLight() }}" alt="Logo" class="h-6 w-auto">
                @endif

                @if ($settings->show_name_in_topbar)
                    <h1 class="text-2xl font-semibold">{{ $settings->app_name; }}</h1>
                @endif
            </a>

            <nav>
                <a href="/"><x-phosphor-house-line class="size-6 text-black/50 dark:text-white/50 hover:text-primary" /></a>
            </nav>
        </div>

        <div class="flex flex-col w-xl h-fit my-auto">
            <main class="fi-simple-main">
                {{ $slot }}
            </main>
        </div>

        <footer class="flex justify-between items-center text-sm w-full px-4 py-2 tracking-wide text-black/50 dark:text-white/50">
            <span>© {{ date('Y') }} - Ministério Público do Estado do Acre</span>
            <span>{{ config('app.version') }}</span>
        </footer>

    </div>
</x-filament-panels::layout.base>

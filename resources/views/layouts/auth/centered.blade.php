@php
    use App\Settings\SystemSettings;

    $settings = app(SystemSettings::class);
@endphp

<x-filament-panels::layout.base>
    <div class="fi-simple-layout">

        <div class="flex flex-col w-xl h-fit my-auto">

            <a href="/" class="flex flex-col items-center gap-8">
                @if ($settings->show_logo_in_topbar)
                    <img src="{{ $settings->getAppLogoLight() }}" alt="Logo" class="h-6 w-auto">
                @endif

                @if ($settings->show_name_in_topbar)
                    <h1 class="text-2xl font-semibold">{{ $settings->app_name; }}</h1>
                @endif
            </a>

            <main class="fi-simple-main">
                {{ $slot }}
            </main>

            <footer class="text-sm mx-auto tracking-wide text-black/50">
                © {{ date('Y') }} - Ministério Público do Estado do Acre - {{ config('app.version') }}
            </footer>
        </div>

    </div>
</x-filament-panels::layout.base>

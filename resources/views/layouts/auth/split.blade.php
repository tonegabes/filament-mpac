@php
    use App\Settings\SystemSettings;

    $settings = app(SystemSettings::class);
@endphp

<x-filament-panels::layout.base>
    <div class="flex min-h-dvh">

        <div class="flex flex-col w-1/2 grow max-h-dvh">
            <img src="{{ $settings->getAuthPageBackground() }}" alt="Login Background" class="w-full h-full object-cover">
        </div>

        <div class="flex flex-col w-1/2 grow">
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

            <div class="flex grow">
                <main class="fi-simple-main w-xl h-fit m-auto" style="margin-block: auto;">
                    {{ $slot }}
                </main>
            </div>

            <footer class="flex justify-between items-center text-sm w-full px-4 py-2 tracking-wide text-black/50 dark:text-white/50">
                <span>© {{ date('Y') }} - Ministério Público do Estado do Acre</span>
                <span>{{ config('app.version') }}</span>
            </footer>
        </div>
    </div>
</x-filament-panels::layout.base>

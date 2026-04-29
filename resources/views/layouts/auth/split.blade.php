@php
    use App\Settings\SystemSettings;

    $settings = app(SystemSettings::class);
@endphp

<x-filament-panels::layout.base>
    <div class="flex min-h-dvh">

        <div class="hidden md:flex flex-col w-1/2 grow max-h-dvh">
            <img src="{{ $settings->getAuthPageBackground() }}" alt="Login Background" class="w-full h-full object-cover">
        </div>

        <div class="flex flex-col w-1/2 grow">
            <div class="flex items-center justify-between gap-4 py-6 px-8 w-full">
                <a href="/" class="flex items-center gap-2">
                    <x-brand-logomark
                        :showAppLogo="$settings->show_app_logo"
                        :appName="$settings->app_name"
                        :appSigla="$settings->app_sigla"
                        :lightLogo="$settings->getAppLogoLight()"
                        :darkLogo="$settings->getAppLogoDark()"
                    />
                </a>

                <nav>
                    <a href="/"><x-phosphor-house-line class="size-6 text-black/50 dark:text-white/50 hover:text-primary" /></a>
                </nav>
            </div>

            <div class="flex grow">
                <main class="fi-simple-main lg:w-xl h-fit m-auto" style="margin-block: auto;">
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

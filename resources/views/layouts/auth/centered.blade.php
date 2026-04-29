@php
    use App\Settings\SystemSettings;

    $settings = app(SystemSettings::class);
@endphp

<x-filament-panels::layout.base>
    <div class="fi-simple-layout">

        <div class="flex flex-col lg:w-xl h-fit my-auto">

            <a href="/" class="flex flex-col items-center dark:text-white gap-8">
                <x-brand-logomark
                    :showAppLogo="$settings->show_app_logo"
                    :appName="$settings->app_name"
                    :appSigla="$settings->app_sigla"
                    :lightLogo="$settings->getAppLogoLight()"
                    :darkLogo="$settings->getAppLogoDark()"
                />
            </a>

            <main class="fi-simple-main">
                {{ $slot }}
            </main>

            <footer class="text-sm mx-auto tracking-wide dark:text-white/50 text-black/50">
                © {{ date('Y') }} - Ministério Público do Estado do Acre - {{ config('app.version') }}
            </footer>
        </div>

    </div>
</x-filament-panels::layout.base>

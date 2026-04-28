<div class="flex items-center gap-2">

    @if ($showAppLogo)
        <img src="{{ $lightLogo }}" alt="Logo" class="w-auto h-5 dark:hidden">
        <img src="{{ $darkLogo }}" alt="Logo" class="w-auto h-5 hidden dark:block">
    @endif

    <div class="flex items-center-safe gap-2">

        @if ($appSigla)
            <div
                @class([
                    'text-3xl font-bold  leading-none dark:border-white/50 ',
                    'pr-2 border-r border-black/10' => $appName,
                ])
            >{{ $appSigla }}</div>
        @endif

        @if ($appName)
            <span
                @class([
                    'text-lg w-full leading-none font-normal tracking-wide font-display text-black/50 dark:text-white/50',
                    'text-sm max-w-[200px] text-balance' => $appSigla,
                ])
            >{{ $appName }}</span>
        @endif

    </div>
</div>

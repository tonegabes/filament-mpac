<div class="flex items-center gap-2">

    @if ($showLogo)
        {{-- Logo para light mode --}}
        <img src="{{ $lightLogo }}" alt="Logo" class="w-auto h-full dark:hidden">
        {{-- Logo para dark mode --}}
        <img src="{{ $darkLogo }}" alt="Logo" class="w-auto h-full hidden dark:block">
    @endif

    @if ($showName)
        <h2>{{ $appName }}</h2>
    @endif
</div>

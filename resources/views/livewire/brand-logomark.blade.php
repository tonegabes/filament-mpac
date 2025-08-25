<div class="flex items-center gap-2">

    {{-- Logo para light mode --}}
    <img src="{{ asset($lightLogo) }}" alt="Logo" class="w-auto h-full dark:hidden">
    {{-- Logo para dark mode --}}
    <img src="{{ asset($darkLogo) }}" alt="Logo" class="w-auto h-full hidden dark:block">
    <h2>{{ $appName }}</h2>
</div>

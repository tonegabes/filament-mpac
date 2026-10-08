<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filament\Panel;

it('registers exactly one default filament panel', function (): void {
    $defaultPanelIds = collect(Filament::getPanels())
        ->filter(fn (Panel $panel): bool => $panel->isDefault())
        ->map(fn (Panel $panel): string => $panel->getId())
        ->values()
        ->all();

    expect($defaultPanelIds)->toHaveCount(1);
});

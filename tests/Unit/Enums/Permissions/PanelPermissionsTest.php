<?php

declare(strict_types=1);

use App\Enums\Permissions\PanelPermissions;
use Filament\Panel;

it('has expected cases and string values', function (): void {
    expect(PanelPermissions::All->value)->toBe('panels.*')
        ->and(PanelPermissions::ViewAdmin->value)->toBe('panels.view.admin')
        ->and(PanelPermissions::ViewApp->value)->toBe('panels.view.app');
});

it('has exactly three cases', function (): void {
    expect(PanelPermissions::cases())->toHaveCount(3);
});

it('resolves panel access permissions from filament panels', function (): void {
    expect(PanelPermissions::fromPanel(Panel::make()->id('admin')))->toBe(PanelPermissions::ViewAdmin)
        ->and(PanelPermissions::fromPanel(Panel::make()->id('unknown')))->toBeNull()
        ->and(PanelPermissions::fromPanel(null))->toBeNull();
});

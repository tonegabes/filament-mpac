<?php

declare(strict_types=1);

use App\Filament\Actions\CopyFileUrlAction;
use App\Models\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns default name copy-file-url', function (): void {
    expect(CopyFileUrlAction::getDefaultName())->toBe('copy-file-url');
});

it('can be made with record and tooltip resolves to string', function (): void {
    $image = Image::create(['name' => 'Test']);
    $action = CopyFileUrlAction::make();
    $action->record($image);

    $tooltip = $action->getTooltip();
    expect($tooltip)->toBeString();
});

it('alpineClickHandler closure produces js containing url when called with record', function (): void {
    $image = Image::create(['name' => 'Test']);
    $action = CopyFileUrlAction::make();
    $action->record($image);

    $handler = $action->getAlpineClickHandler();
    expect($handler)->toBeString();
});

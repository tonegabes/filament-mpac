<?php

declare(strict_types=1);

use App\Enums\Permissions\ActivityPermissions;

it('has expected cases and string values', function (): void {
    expect(ActivityPermissions::All->value)->toBe('activities.*')
        ->and(ActivityPermissions::ViewAny->value)->toBe('activities.view.any')
        ->and(ActivityPermissions::View->value)->toBe('activities.view');
});

it('has exactly three cases', function (): void {
    expect(ActivityPermissions::cases())->toHaveCount(3);
});

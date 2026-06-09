<?php

declare(strict_types=1);

use App\Enums\Permissions\WildcardPermissions;

it('has expected cases and string values', function (): void {
    expect(WildcardPermissions::All->value)->toBe('*')
        ->and(WildcardPermissions::ViewAny->value)->toBe('*.view.any')
        ->and(WildcardPermissions::UpdateOwn->value)->toBe('*.update.own');
});

it('has exactly three cases', function (): void {
    expect(WildcardPermissions::cases())->toHaveCount(3);
});

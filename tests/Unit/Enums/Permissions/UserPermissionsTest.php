<?php

declare(strict_types=1);

use App\Enums\Permissions\UserPermissions;

it('has expected cases and string values', function (): void {
    expect(UserPermissions::All->value)->toBe('users.*')
        ->and(UserPermissions::ViewAny->value)->toBe('users.view.any')
        ->and(UserPermissions::View->value)->toBe('users.view')
        ->and(UserPermissions::Create->value)->toBe('users.create')
        ->and(UserPermissions::Update->value)->toBe('users.update')
        ->and(UserPermissions::Delete->value)->toBe('users.delete')
        ->and(UserPermissions::Restore->value)->toBe('users.restore')
        ->and(UserPermissions::ForceDelete->value)->toBe('users.force-delete')
        ->and(UserPermissions::Replicate->value)->toBe('users.replicate')
        ->and(UserPermissions::Reorder->value)->toBe('users.reorder');
});

it('has exactly ten cases', function (): void {
    expect(UserPermissions::cases())->toHaveCount(10);
});

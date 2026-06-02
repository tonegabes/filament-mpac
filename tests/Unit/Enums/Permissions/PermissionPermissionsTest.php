<?php

declare(strict_types=1);

use App\Enums\Permissions\PermissionPermissions;

it('has expected cases and string values', function (): void {
    expect(PermissionPermissions::All->value)->toBe('permissions.*')
        ->and(PermissionPermissions::ViewAny->value)->toBe('permissions.view.any')
        ->and(PermissionPermissions::View->value)->toBe('permissions.view')
        ->and(PermissionPermissions::Create->value)->toBe('permissions.create')
        ->and(PermissionPermissions::Update->value)->toBe('permissions.update')
        ->and(PermissionPermissions::Delete->value)->toBe('permissions.delete')
        ->and(PermissionPermissions::Restore->value)->toBe('permissions.restore')
        ->and(PermissionPermissions::ForceDelete->value)->toBe('permissions.force-delete')
        ->and(PermissionPermissions::Replicate->value)->toBe('permissions.replicate')
        ->and(PermissionPermissions::Reorder->value)->toBe('permissions.reorder');
});

it('has exactly ten cases', function (): void {
    expect(PermissionPermissions::cases())->toHaveCount(10);
});

<?php

declare(strict_types=1);

use App\Enums\Permissions\PermissionPermissions;

it('has expected cases and string values', function (): void {
    expect(PermissionPermissions::All->value)->toBe('permissions')
        ->and(PermissionPermissions::View->value)->toBe('permissions.view')
        ->and(PermissionPermissions::Create->value)->toBe('permissions.create')
        ->and(PermissionPermissions::Update->value)->toBe('permissions.update')
        ->and(PermissionPermissions::Delete->value)->toBe('permissions.delete')
        ->and(PermissionPermissions::Restore->value)->toBe('permissions.restore')
        ->and(PermissionPermissions::ForceDelete->value)->toBe('permissions.force-delete');
});

it('has exactly seven cases', function (): void {
    expect(PermissionPermissions::cases())->toHaveCount(7);
});

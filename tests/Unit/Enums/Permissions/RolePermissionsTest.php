<?php

declare(strict_types=1);

use App\Enums\Permissions\RolePermissions;

it('has expected cases and string values', function (): void {
    expect(RolePermissions::All->value)->toBe('roles')
        ->and(RolePermissions::View->value)->toBe('roles.view')
        ->and(RolePermissions::Create->value)->toBe('roles.create')
        ->and(RolePermissions::Update->value)->toBe('roles.update')
        ->and(RolePermissions::Delete->value)->toBe('roles.delete')
        ->and(RolePermissions::Restore->value)->toBe('roles.restore')
        ->and(RolePermissions::ForceDelete->value)->toBe('roles.force-delete');
});

it('has exactly seven cases', function (): void {
    expect(RolePermissions::cases())->toHaveCount(7);
});

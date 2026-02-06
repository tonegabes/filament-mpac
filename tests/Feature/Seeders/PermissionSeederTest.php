<?php

declare(strict_types=1);

use App\Enums\Permissions\PermissionPermissions;
use App\Enums\Permissions\RolePermissions;
use App\Enums\Permissions\SystemPermissions;
use App\Enums\Permissions\UserPermissions;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates all permissions from enums', function (): void {
    $this->seed(Database\Seeders\PermissionSeeder::class);

    $expected = array_merge(
        array_map(fn ($c) => $c->value, SystemPermissions::cases()),
        array_map(fn ($c) => $c->value, UserPermissions::cases()),
        array_map(fn ($c) => $c->value, RolePermissions::cases()),
        array_map(fn ($c) => $c->value, PermissionPermissions::cases()),
    );

    foreach ($expected as $name) {
        $this->assertDatabaseHas(Permission::class, ['name' => $name]);
    }

    expect(Permission::count())->toBe(count($expected));
});

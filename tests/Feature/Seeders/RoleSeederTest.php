<?php

declare(strict_types=1);

use App\Enums\Permissions\PermissionPermissions;
use App\Enums\Permissions\RolePermissions;
use App\Enums\Permissions\SystemPermissions;
use App\Enums\Permissions\UserPermissions;
use App\Enums\Roles;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Database\Seeders\PermissionSeeder::class);
});

it('creates Developer Admin and Operator roles', function (): void {
    $this->seed(Database\Seeders\RoleSeeder::class);

    expect(Role::where('name', Roles::Developer->value)->exists())->toBeTrue()
        ->and(Role::where('name', Roles::Admin->value)->exists())->toBeTrue()
        ->and(Role::where('name', Roles::Operator->value)->exists())->toBeTrue();
});

it('assigns all permissions to Developer role', function (): void {
    $this->seed(Database\Seeders\RoleSeeder::class);

    $developer = Role::where('name', Roles::Developer->value)->first();
    expect($developer)->not->toBeNull();

    $allPermissions = array_merge(
        SystemPermissions::cases(),
        UserPermissions::cases(),
        RolePermissions::cases(),
        PermissionPermissions::cases(),
    );
    foreach ($allPermissions as $perm) {
        expect($developer->hasPermissionTo($perm->value))->toBeTrue();
    }
});

it('assigns PanelsViewAdmin and UserPermissions to Admin role', function (): void {
    $this->seed(Database\Seeders\RoleSeeder::class);

    $admin = Role::where('name', Roles::Admin->value)->first();
    expect($admin)->not->toBeNull();
    expect($admin->hasPermissionTo(SystemPermissions::PanelsViewAdmin->value))->toBeTrue();
    expect($admin->hasPermissionTo(UserPermissions::All->value))->toBeTrue();
});

it('assigns PanelsViewOperator to Operator role', function (): void {
    $this->seed(Database\Seeders\RoleSeeder::class);

    $operator = Role::where('name', Roles::Operator->value)->first();
    expect($operator)->not->toBeNull();
    expect($operator->hasPermissionTo(SystemPermissions::PanelsViewOperator->value))->toBeTrue();
});

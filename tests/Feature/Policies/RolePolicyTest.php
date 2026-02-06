<?php

declare(strict_types=1);

use App\Enums\Permissions\RolePermissions;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->make(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    foreach (RolePermissions::cases() as $perm) {
        Permission::firstOrCreate(['name' => $perm->value]);
    }
});

it('allows viewAny when user has roles permission', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(RolePermissions::All->value);

    expect($user->can('viewAny', Role::class))->toBeTrue();
});

it('denies viewAny when user lacks roles permission', function (): void {
    $user = User::factory()->create();

    expect($user->can('viewAny', Role::class))->toBeFalse();
});

it('allows view when user has roles.view permission', function (): void {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
    $user->givePermissionTo(RolePermissions::View->value);

    expect($user->can('view', $role))->toBeTrue();
});

it('allows create when user has roles.create permission', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(RolePermissions::Create->value);

    expect($user->can('create', Role::class))->toBeTrue();
});

it('allows update when user has roles.update permission', function (): void {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
    $user->givePermissionTo(RolePermissions::Update->value);

    expect($user->can('update', $role))->toBeTrue();
});

it('denies update when user lacks roles.update permission', function (): void {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

    expect($user->can('update', $role))->toBeFalse();
});

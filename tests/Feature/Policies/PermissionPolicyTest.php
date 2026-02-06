<?php

declare(strict_types=1);

use App\Enums\Permissions\PermissionPermissions;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->make(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    foreach (PermissionPermissions::cases() as $perm) {
        Permission::firstOrCreate(['name' => $perm->value]);
    }
});

it('allows viewAny when user has permissions permission', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionPermissions::All->value);

    expect($user->can('viewAny', Permission::class))->toBeTrue();
});

it('denies viewAny when user lacks permissions permission', function (): void {
    $user = User::factory()->create();

    expect($user->can('viewAny', Permission::class))->toBeFalse();
});

it('allows view when user has permissions.view permission', function (): void {
    $user = User::factory()->create();
    $perm = Permission::firstOrCreate(['name' => 'test.permission']);
    $user->givePermissionTo(PermissionPermissions::View->value);

    expect($user->can('view', $perm))->toBeTrue();
});

it('allows create when user has permissions.create permission', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionPermissions::Create->value);

    expect($user->can('create', Permission::class))->toBeTrue();
});

it('denies update when user lacks permissions.update permission', function (): void {
    $user = User::factory()->create();
    $perm = Permission::firstOrCreate(['name' => 'test.permission']);

    expect($user->can('update', $perm))->toBeFalse();
});

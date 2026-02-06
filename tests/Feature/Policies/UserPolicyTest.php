<?php

declare(strict_types=1);

use App\Enums\Permissions\UserPermissions;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->make(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    foreach (UserPermissions::cases() as $perm) {
        Permission::firstOrCreate(['name' => $perm->value]);
    }
});

it('allows viewAny when user has users permission', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(UserPermissions::All->value);

    expect($user->can('viewAny', User::class))->toBeTrue();
});

it('denies viewAny when user lacks users permission', function (): void {
    $user = User::factory()->create();

    expect($user->can('viewAny', User::class))->toBeFalse();
});

it('allows view when user has users.view permission', function (): void {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $user->givePermissionTo(UserPermissions::View->value);

    expect($user->can('view', $target))->toBeTrue();
});

it('allows create when user has users.create permission', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(UserPermissions::Create->value);

    expect($user->can('create', User::class))->toBeTrue();
});

it('allows update when user has users.update permission', function (): void {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $user->givePermissionTo(UserPermissions::Update->value);

    expect($user->can('update', $target))->toBeTrue();
});

it('allows delete when user has users.delete permission', function (): void {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $user->givePermissionTo(UserPermissions::Delete->value);

    expect($user->can('delete', $target))->toBeTrue();
});

it('denies update when user lacks users.update permission', function (): void {
    $user = User::factory()->create();
    $target = User::factory()->create();

    expect($user->can('update', $target))->toBeFalse();
});

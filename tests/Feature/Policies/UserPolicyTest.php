<?php

declare(strict_types=1);

use App\Enums\Permissions\UserPermissions;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->make(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    foreach (UserPermissions::cases() as $permission) {
        Permission::firstOrCreate(['name' => $permission->value]);
    }
});

it('allows viewAny when user has users.view.any permission', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(UserPermissions::ViewAny->value);

    expect($user->can('viewAny', User::class))->toBeTrue();
});

it('allows viewAny when user has users wildcard permission', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(UserPermissions::All->value);

    expect($user->can('viewAny', User::class))->toBeTrue();
});

it('denies viewAny when user lacks users.view.any permission', function (): void {
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

it('denies view when user lacks users.view permission', function (): void {
    $user = User::factory()->create();
    $target = User::factory()->create();

    expect($user->can('view', $target))->toBeFalse();
});

it('denies self-deletion', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(UserPermissions::Delete->value);

    expect($user->can('delete', $user))->toBeFalse();
});

it('denies non-developer from updating or deleting a developer', function (): void {
    $devRole = App\Models\Role::firstOrCreate(['name' => App\Enums\UserRole::Developer->value]);
    $admin = User::factory()->create();
    $admin->givePermissionTo([UserPermissions::Update->value, UserPermissions::Delete->value]);

    $developer = User::factory()->create();
    $developer->assignRole($devRole);

    expect($admin->can('update', $developer))->toBeFalse()
        ->and($admin->can('delete', $developer))->toBeFalse();
});

it('denies deleting the last developer', function (): void {
    Permission::firstOrCreate(['name' => App\Enums\Permissions\WildcardPermissions::All->value]);
    $devRole = App\Models\Role::firstOrCreate(['name' => App\Enums\UserRole::Developer->value]);
    $actor = User::factory()->create();
    $actor->givePermissionTo([
        App\Enums\Permissions\WildcardPermissions::All->value,
        UserPermissions::Delete->value,
    ]);

    $lastDeveloper = User::factory()->create();
    $lastDeveloper->assignRole($devRole);

    expect(app(App\Policies\UserPolicy::class)->delete($actor, $lastDeveloper))->toBeFalse()
        ->and($lastDeveloper->delete())->toBeFalse();
});

<?php

declare(strict_types=1);

use App\Enums\Permissions\SystemPermissions;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->make(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
});

it('allows all gates when user has TheOneAboveAll role', function (): void {
    $role = Role::create(['name' => 'TheOneAboveAll', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);

    expect(Gate::forUser($user)->allows('viewActivityLogUi'))->toBeTrue();
});

it('allows viewActivityLogUi when user has ViewActivityLog permission', function (): void {
    Permission::firstOrCreate(['name' => SystemPermissions::ViewActivityLog->value]);
    $user = User::factory()->create();
    $user->givePermissionTo(SystemPermissions::ViewActivityLog->value);

    expect(Gate::forUser($user)->allows('viewActivityLogUi'))->toBeTrue();
});

it('denies viewActivityLogUi when user lacks permission', function (): void {
    $user = User::factory()->create();

    expect(Gate::forUser($user)->allows('viewActivityLogUi'))->toBeFalse();
});

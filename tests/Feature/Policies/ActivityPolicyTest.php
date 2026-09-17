<?php

declare(strict_types=1);

use App\Enums\Permissions\ActivityPermissions;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->make(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    foreach (ActivityPermissions::cases() as $permission) {
        Permission::firstOrCreate(['name' => $permission->value]);
    }
});

it('allows viewAny when user has activities.view.any permission', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(ActivityPermissions::ViewAny->value);

    expect($user->can('viewAny', Activity::class))->toBeTrue();
});

it('allows viewAny when user has activities wildcard permission', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(ActivityPermissions::All->value);

    expect($user->can('viewAny', Activity::class))->toBeTrue();
});

it('denies viewAny when user lacks activities.view.any permission', function (): void {
    $user = User::factory()->create();

    expect($user->can('viewAny', Activity::class))->toBeFalse();
});

it('allows view when user has activities.view permission', function (): void {
    $user = User::factory()->create();
    $activity = Activity::query()->firstOrFail();
    $user->givePermissionTo(ActivityPermissions::View->value);

    expect($user->can('view', $activity))->toBeTrue();
});

it('denies view when user lacks activities.view permission', function (): void {
    $user = User::factory()->create();
    $activity = Activity::query()->firstOrFail();

    expect($user->can('view', $activity))->toBeFalse();
});

it('denies create update and delete', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(ActivityPermissions::All->value);
    $activity = Activity::query()->firstOrFail();

    expect($user->can('create', Activity::class))->toBeFalse()
        ->and($user->can('update', $activity))->toBeFalse()
        ->and($user->can('delete', $activity))->toBeFalse();
});

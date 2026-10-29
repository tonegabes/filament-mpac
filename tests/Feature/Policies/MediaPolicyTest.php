<?php

declare(strict_types=1);

use App\Enums\Permissions\MediaPermissions;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->make(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    foreach (MediaPermissions::cases() as $permission) {
        Permission::firstOrCreate(['name' => $permission->value]);
    }
});

it('allows viewAny when user has media.view.any permission', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(MediaPermissions::ViewAny->value);

    expect($user->can('viewAny', Media::class))->toBeTrue();
});

it('denies viewAny when user lacks media.view.any permission', function (): void {
    $user = User::factory()->create();

    expect($user->can('viewAny', Media::class))->toBeFalse();
});

it('allows delete when user has media.delete permission', function (): void {
    $user = User::factory()->create();
    $media = new Media;
    $user->givePermissionTo(MediaPermissions::Delete->value);

    expect($user->can('delete', $media))->toBeTrue();
});

it('denies delete when user lacks media.delete permission', function (): void {
    $user = User::factory()->create();
    $media = new Media;

    expect($user->can('delete', $media))->toBeFalse();
});

it('denies bulk delete when user lacks media.delete permission', function (): void {
    $user = User::factory()->create();

    expect($user->can('deleteAny', Media::class))->toBeFalse();
});

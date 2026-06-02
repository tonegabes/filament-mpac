<?php

declare(strict_types=1);

use App\Enums\Permissions\ImagePermissions;
use App\Models\Image;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->make(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    foreach (ImagePermissions::cases() as $permission) {
        Permission::firstOrCreate(['name' => $permission->value]);
    }
});

it('allows viewAny when user has images.view.any permission', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(ImagePermissions::ViewAny->value);

    expect($user->can('viewAny', Image::class))->toBeTrue();
});

it('allows viewAny when user has images wildcard permission', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(ImagePermissions::All->value);

    expect($user->can('viewAny', Image::class))->toBeTrue();
});

it('denies viewAny when user lacks images.view.any permission', function (): void {
    $user = User::factory()->create();

    expect($user->can('viewAny', Image::class))->toBeFalse();
});

it('allows view when user has images.view permission', function (): void {
    $user = User::factory()->create();
    $image = Image::create(['name' => 'Imagem']);
    $user->givePermissionTo(ImagePermissions::View->value);

    expect($user->can('view', $image))->toBeTrue();
});

it('allows update when user has images.update permission', function (): void {
    $user = User::factory()->create();
    $image = Image::create(['name' => 'Imagem']);
    $user->givePermissionTo(ImagePermissions::Update->value);

    expect($user->can('update', $image))->toBeTrue();
});

it('denies update when user lacks images.update permission', function (): void {
    $user = User::factory()->create();
    $image = Image::create(['name' => 'Imagem']);

    expect($user->can('update', $image))->toBeFalse();
});

it('denies view when user lacks images.view permission', function (): void {
    $user = User::factory()->create();
    $image = Image::create(['name' => 'Imagem']);

    expect($user->can('view', $image))->toBeFalse();
});

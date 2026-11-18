<?php

declare(strict_types=1);

use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mpac\FilamentAccessControl\Enums\Permissions\PanelPermissions;
use Mpac\FilamentAccessControl\Enums\Permissions\PermissionPermissions;
use Mpac\FilamentAccessControl\Enums\Permissions\RolePermissions;
use Mpac\FilamentAccessControl\Enums\Permissions\UserPermissions;
use Mpac\FilamentAccessControl\Enums\Permissions\WildcardPermissions;
use Mpac\FilamentAccessControl\Enums\UserRole;
use Mpac\FilamentAccessControl\Models\Role;
use Mpac\FilamentAudit\Enums\Permissions\ActivityPermissions;
use Mpac\FilamentBranding\Enums\SystemPermissions;
use Mpac\FilamentFileLibrary\Enums\Permissions\DocumentPermissions;
use Mpac\FilamentFileLibrary\Enums\Permissions\ImagePermissions;
use Mpac\FilamentFileLibrary\Enums\Permissions\MediaPermissions;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PermissionSeeder::class);
});

it('creates Developer Admin and User roles', function (): void {
    $this->seed(RoleSeeder::class);

    expect(Role::where('name', UserRole::Developer->value)->exists())->toBeTrue()
        ->and(Role::where('name', UserRole::Admin->value)->exists())->toBeTrue();
});

it('assigns all permissions to Developer role', function (): void {
    $this->seed(RoleSeeder::class);

    $developer = Role::where('name', UserRole::Developer->value)->firstOrFail();

    $allPermissions = array_merge(
        WildcardPermissions::cases(),
        PanelPermissions::cases(),
        SystemPermissions::cases(),
        UserPermissions::cases(),
        RolePermissions::cases(),
        PermissionPermissions::cases(),
        DocumentPermissions::cases(),
        ImagePermissions::cases(),
        MediaPermissions::cases(),
        ActivityPermissions::cases(),
    );

    foreach ($allPermissions as $perm) {
        expect($developer->hasPermissionTo($perm->value))->toBeTrue();
    }
});

it('assigns panel access and main resource permissions to Admin role', function (): void {
    $this->seed(RoleSeeder::class);

    $admin = Role::where('name', UserRole::Admin->value)->firstOrFail();

    expect($admin->hasPermissionTo(PanelPermissions::ViewAdmin->value))->toBeTrue()
        ->and($admin->hasPermissionTo(UserPermissions::All->value))->toBeTrue()
        ->and($admin->hasPermissionTo(DocumentPermissions::All->value))->toBeTrue()
        ->and($admin->hasPermissionTo(ImagePermissions::All->value))->toBeTrue()
        ->and($admin->hasPermissionTo(MediaPermissions::All->value))->toBeTrue()
        ->and($admin->hasPermissionTo(ActivityPermissions::All->value))->toBeTrue();
});

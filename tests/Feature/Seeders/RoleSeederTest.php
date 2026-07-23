<?php

declare(strict_types=1);

use App\Enums\Permissions\DocumentPermissions;
use App\Enums\Permissions\ImagePermissions;
use App\Enums\Permissions\PanelPermissions;
use App\Enums\Permissions\PermissionPermissions;
use App\Enums\Permissions\RolePermissions;
use App\Enums\Permissions\SystemPermissions;
use App\Enums\Permissions\UserPermissions;
use App\Enums\Permissions\WildcardPermissions;
use App\Enums\UserRole;
use App\Models\Role;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
    );
    foreach ($allPermissions as $perm) {
        expect($developer->hasPermissionTo($perm->value))->toBeTrue();
    }
});

it('assigns panel access and main resource permissions to Admin role', function (): void {
    $this->seed(RoleSeeder::class);

    $admin = Role::where('name', UserRole::Admin->value)->firstOrFail();
    expect($admin->hasPermissionTo(PanelPermissions::ViewAdmin->value))->toBeTrue();
    expect($admin->hasPermissionTo(UserPermissions::All->value))->toBeTrue();
    expect($admin->hasPermissionTo(DocumentPermissions::All->value))->toBeTrue();
    expect($admin->hasPermissionTo(ImagePermissions::All->value))->toBeTrue();
});

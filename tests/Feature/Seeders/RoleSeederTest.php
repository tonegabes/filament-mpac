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
use App\Enums\Roles;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Database\Seeders\PermissionSeeder::class);
});

it('creates Developer Admin and User roles', function (): void {
    $this->seed(Database\Seeders\RoleSeeder::class);

    expect(Role::where('name', Roles::Developer->value)->exists())->toBeTrue()
        ->and(Role::where('name', Roles::Admin->value)->exists())->toBeTrue();
});

it('assigns all permissions to Developer role', function (): void {
    $this->seed(Database\Seeders\RoleSeeder::class);

    $developer = Role::where('name', Roles::Developer->value)->firstOrFail();

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
    $this->seed(Database\Seeders\RoleSeeder::class);

    $admin = Role::where('name', Roles::Admin->value)->firstOrFail();
    expect($admin->hasPermissionTo(PanelPermissions::ViewAdmin->value))->toBeTrue();
    expect($admin->hasPermissionTo(UserPermissions::All->value))->toBeTrue();
    expect($admin->hasPermissionTo(DocumentPermissions::All->value))->toBeTrue();
    expect($admin->hasPermissionTo(ImagePermissions::All->value))->toBeTrue();
});

<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Filament\Resources\Permissions\Pages\CreatePermission;
use App\Filament\Resources\Permissions\Pages\ListPermissions;
use App\Models\Permission;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
    $this->seed(PermissionSeeder::class);
    $this->seed(RoleSeeder::class);

    $developer = User::factory()->create();
    $developer->assignRole(UserRole::Developer->value);

    $this->actingAs($developer);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('can render list permissions page and see records', function (): void {
    $permissions = Permission::limit(5)->get();

    Livewire::test(ListPermissions::class)
        ->assertCanSeeTableRecords($permissions);
});

it('can create a permission', function (): void {
    Livewire::test(CreatePermission::class)
        ->fillForm([
            'name' => 'custom.permission',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Permission::class, [
        'name' => 'custom.permission',
    ]);
});

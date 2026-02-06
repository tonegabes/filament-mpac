<?php

declare(strict_types=1);

use App\Enums\Roles;
use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->make(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    $this->seed(Database\Seeders\PermissionSeeder::class);
    $this->seed(Database\Seeders\RoleSeeder::class);

    $user = User::factory()->create();
    $user->assignRole(Roles::Developer->value);

    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('can render list roles page and see records', function (): void {
    $roles = Role::all();

    Livewire::test(ListRoles::class)
        ->assertCanSeeTableRecords($roles);
});

it('can create a role', function (): void {
    $permissions = Permission::limit(2)->pluck('id')->toArray();

    Livewire::test(CreateRole::class)
        ->fillForm([
            'name'        => 'Editor',
            'permissions' => $permissions,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Role::class, [
        'name' => 'Editor',
    ]);
});

it('can edit a role', function (): void {
    $role = Role::first();
    $newName = 'Updated Role Name';

    Livewire::test(EditRole::class, ['record' => $role->getRouteKey()])
        ->fillForm([
            'name' => $newName,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($role->refresh()->name)->toBe($newName);
});

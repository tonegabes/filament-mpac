<?php

declare(strict_types=1);

use App\Enums\Roles;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
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

    $admin = User::factory()->create();
    $admin->assignRole(Roles::Admin->value);

    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('can render list users page and see records', function (): void {
    $users = User::factory()->count(3)->create();

    Livewire::test(ListUsers::class)
        ->assertCanSeeTableRecords($users);
});

it('can list and search users', function (): void {
    $user = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);

    Livewire::test(ListUsers::class)
        ->searchTable('John')
        ->assertCanSeeTableRecords([$user])
        ->searchTable('unknown')
        ->assertCanNotSeeTableRecords([$user]);
});

it('can create a user', function (): void {
    $role = Role::first();

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name'      => 'New User',
            'email'     => 'newuser@example.com',
            'username'  => 'newuser',
            'is_active' => true,
            'roles'     => [$role->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(User::class, [
        'name'      => 'New User',
        'email'     => 'newuser@example.com',
        'username'  => 'newuser',
        'is_active' => true,
    ]);
});

it('can edit a user', function (): void {
    $user = User::factory()->create(['name' => 'Original Name']);
    $role = Role::first();
    $user->assignRole($role);

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->refresh()->name)->toBe('Updated Name');
});

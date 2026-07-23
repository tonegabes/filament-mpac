<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Role;
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

    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);

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
    $role = Role::query()->firstOrFail();

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'username' => 'newuser',
            'is_active' => true,
            'roles' => [$role->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(User::class, [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'username' => 'newuser',
        'is_active' => true,
    ]);
});

it('can edit a user', function (): void {
    $user = User::factory()->create(['name' => 'Original Name']);
    $role = Role::query()->firstOrFail();
    $user->assignRole($role);

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->refresh()->name)->toBe('Updated Name');
});

it('denies list access when operator role lacks users.view.any permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole(UserRole::User->value);

    $this->actingAs($user);

    Livewire::test(ListUsers::class)
        ->assertForbidden();
});

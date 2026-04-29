<?php

declare(strict_types=1);

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->make(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    $this->seed(Database\Seeders\PermissionSeeder::class);
    $this->seed(Database\Seeders\RoleSeeder::class);
});

it('creates starter users with their expected roles', function (): void {
    $this->seed(Database\Seeders\UserSeeder::class);

    $developer = User::where('email', 'developer@email.com')->firstOrFail();
    $admin = User::where('email', 'admin@email.com')->firstOrFail();
    $operator = User::where('email', 'operator@email.com')->firstOrFail();

    expect($developer->hasRole(Roles::Developer->value))->toBeTrue()
        ->and($developer->hasRole(Roles::Admin->value))->toBeTrue()
        ->and($admin->hasRole(Roles::Admin->value))->toBeTrue()
        ->and($operator->hasRole(Roles::Operator->value))->toBeTrue();
});

it('can run repeatedly without duplicating starter users', function (): void {
    $this->seed(Database\Seeders\UserSeeder::class);
    $this->seed(Database\Seeders\UserSeeder::class);

    expect(User::whereIn('email', [
        'developer@email.com',
        'admin@email.com',
        'operator@email.com',
    ])->count())->toBe(3);
});

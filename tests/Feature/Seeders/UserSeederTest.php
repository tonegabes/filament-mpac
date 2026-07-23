<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
    $this->seed(PermissionSeeder::class);
    $this->seed(RoleSeeder::class);
});

it('creates starter users with their expected roles', function (): void {
    $this->seed(UserSeeder::class);

    $developer = User::where('email', 'developer@email.com')->firstOrFail();
    $admin = User::where('email', 'admin@email.com')->firstOrFail();

    expect($developer->hasRole(UserRole::Developer->value))->toBeTrue()
        ->and($developer->hasRole(UserRole::Admin->value))->toBeTrue()
        ->and($admin->hasRole(UserRole::Admin->value))->toBeTrue();
});

it('can run repeatedly without duplicating starter users', function (): void {
    $this->seed(UserSeeder::class);
    $this->seed(UserSeeder::class);

    expect(User::whereIn('email', [
        'developer@email.com',
        'admin@email.com',
    ])->count())->toBe(2);
});

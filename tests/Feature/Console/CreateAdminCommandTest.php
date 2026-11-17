<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\DevelopmentSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\PendingCommand;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

it('creates an admin user via mpac:create-admin command', function (): void {
    $this->seed(PermissionSeeder::class);
    $this->seed(RoleSeeder::class);

    $command = artisan('mpac:create-admin', [
        '--name' => 'Prod Admin',
        '--username' => 'prodadmin',
        '--email' => 'prodadmin@mpac.mp.br',
        '--password' => 'StrongPass123!',
    ]);
    assert($command instanceof PendingCommand);
    $command->assertSuccessful()->run();

    $user = User::where('username', 'prodadmin')->firstOrFail();

    expect($user->name)->toBe('Prod Admin')
        ->and($user->is_active)->toBeTrue()
        ->and(Hash::check('StrongPass123!', (string) $user->password))->toBeTrue()
        ->and($user->hasRole(UserRole::Admin->value))->toBeTrue();
});

it('aborts DevelopmentSeeder and UserSeeder in production', function (): void {
    $this->app->detectEnvironment(fn (): string => 'production');

    expect(fn () => (new DevelopmentSeeder)->run())
        ->toThrow(RuntimeException::class)
        ->and(fn () => (new UserSeeder)->run())
        ->toThrow(RuntimeException::class);
});

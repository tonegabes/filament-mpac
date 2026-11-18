<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Mpac\FilamentAccessControl\Enums\UserRole;
use RuntimeException;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('UserSeeder cannot be executed in production.');
        }

        // Developer
        $userDeveloper = User::firstOrCreate(
            [
                'username' => 'developer',
                'email' => 'developer@email.com',
            ],
            [
                'name' => 'Desenvolvedor',
                'password' => Hash::make('developer'),
                'is_active' => true,
            ],
        );
        $userDeveloper->syncRoles([
            UserRole::Developer->value,
            UserRole::Admin->value,
        ]);

        // Admin
        $userAdmin = User::firstOrCreate(
            [
                'username' => 'admin',
                'email' => 'admin@email.com',
            ],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin'),
                'is_active' => true,
            ],
        );
        $userAdmin->syncRoles([
            UserRole::Admin->value,
        ]);

        // User
        $userUser = User::firstOrCreate(
            [
                'username' => 'user',
                'email' => 'user@email.com',
            ],
            [
                'name' => 'Usuário',
                'password' => Hash::make('user'),
                'is_active' => true,
            ],
        );
        $userUser->syncRoles([UserRole::User->value]);
    }
}

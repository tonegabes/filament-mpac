<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Developer
        $userDeveloper = User::firstOrCreate(
            [
                'username' => 'developer',
                'email'    => 'developer@email.com',
            ],
            [
                'name'      => 'Desenvolvedor',
                'password'  => Hash::make('developer'),
                'is_active' => true,
            ]
        );
        $userDeveloper->syncRoles([
            Roles::Developer,
            Roles::Admin,
        ]);

        // Admin
        $userAdmin = User::firstOrCreate(
            [
                'username' => 'admin',
                'email'    => 'admin@email.com',
            ],
            [
                'name'      => 'Administrador',
                'password'  => Hash::make('admin'),
                'is_active' => true,
            ]
        );
        $userAdmin->syncRoles([
            Roles::Admin,
        ]);

        // Operator
        $userOperator = User::firstOrCreate(
            [
                'username' => 'operator',
                'email'    => 'operator@email.com',
            ],
            [
                'name'      => 'Operador',
                'password'  => Hash::make('operator'),
                'is_active' => true,
            ],
        );
        $userOperator->syncRoles([Roles::Operator]);

    }
}

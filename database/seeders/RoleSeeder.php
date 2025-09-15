<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permissions\PermissionPermissions;
use App\Enums\Permissions\RolePermissions;
use App\Enums\Permissions\SystemPermissions;
use App\Enums\Permissions\UserPermissions;
use App\Enums\Roles;
use App\Models\Role;
use Illuminate\Database\Seeder;

final class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roleDeveloper = Role::firstOrCreate(['name' => Roles::Developer->value]);
        $roleAdmin = Role::firstOrCreate(['name' => Roles::Admin->value]);
        $roleOperator = Role::firstOrCreate(['name' => Roles::Operator->value]);

        $roleDeveloper->syncPermissions([
            ...SystemPermissions::cases(),
            ...UserPermissions::cases(),
            ...RolePermissions::cases(),
            ...PermissionPermissions::cases(),
        ]);

        $roleAdmin->syncPermissions([
            SystemPermissions::PanelsViewAdmin,
            ...UserPermissions::cases(),
        ]);

        $roleOperator->syncPermissions([
            SystemPermissions::PanelsViewOperator,
        ]);
    }
}

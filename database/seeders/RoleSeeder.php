<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permissions\DocumentPermissions;
use App\Enums\Permissions\ImagePermissions;
use App\Enums\Permissions\PanelPermissions;
use App\Enums\Permissions\PermissionPermissions;
use App\Enums\Permissions\RolePermissions;
use App\Enums\Permissions\SystemPermissions;
use App\Enums\Permissions\UserPermissions;
use App\Enums\Permissions\WildcardPermissions;
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
            ...WildcardPermissions::cases(),
            ...SystemPermissions::cases(),
            ...PanelPermissions::cases(),
            ...UserPermissions::cases(),
            ...RolePermissions::cases(),
            ...PermissionPermissions::cases(),
            ...DocumentPermissions::cases(),
            ...ImagePermissions::cases(),
        ]);

        $roleAdmin->syncPermissions([
            PanelPermissions::ViewAdmin,
            UserPermissions::All,
            DocumentPermissions::All,
            ImagePermissions::All,
        ]);

        $roleOperator->syncPermissions([
            PanelPermissions::ViewAdmin,
            DocumentPermissions::All,
            ImagePermissions::All,
        ]);
    }
}

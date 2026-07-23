<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permissions\DocumentPermissions;
use App\Enums\Permissions\ImagePermissions;
use App\Enums\Permissions\PanelPermissions;
use App\Enums\Permissions\UserPermissions;
use App\Enums\UserRole;
use App\Models\Role;
use Illuminate\Database\Seeder;

final class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roleDeveloper = Role::firstOrCreate(['name' => UserRole::Developer->value]);
        $roleAdmin = Role::firstOrCreate(['name' => UserRole::Admin->value]);
        $roleUser = Role::firstOrCreate(['name' => UserRole::User->value]);

        $roleDeveloper->syncPermissions(['*']);

        $roleAdmin->syncPermissions([
            PanelPermissions::ViewAdmin,
            UserPermissions::All,
            DocumentPermissions::All,
            ImagePermissions::All,
        ]);

        $roleUser->syncPermissions([
            PanelPermissions::ViewApp,
        ]);
    }
}

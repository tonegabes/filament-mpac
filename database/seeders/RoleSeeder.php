<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Mpac\FilamentAccessControl\Enums\Permissions\PanelPermissions;
use Mpac\FilamentAccessControl\Enums\Permissions\UserPermissions;
use Mpac\FilamentAccessControl\Enums\Permissions\WildcardPermissions;
use Mpac\FilamentAccessControl\Enums\UserRole;
use Mpac\FilamentAccessControl\Models\Role;
use Mpac\FilamentAudit\Enums\Permissions\ActivityPermissions;
use Mpac\FilamentFileLibrary\Enums\Permissions\DocumentPermissions;
use Mpac\FilamentFileLibrary\Enums\Permissions\ImagePermissions;
use Mpac\FilamentFileLibrary\Enums\Permissions\MediaPermissions;

final class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roleDeveloper = Role::firstOrCreate(['name' => UserRole::Developer->value]);
        $roleAdmin = Role::firstOrCreate(['name' => UserRole::Admin->value]);
        $roleUser = Role::firstOrCreate(['name' => UserRole::User->value]);

        $roleDeveloper->syncPermissions([WildcardPermissions::All->value]);

        $roleAdmin->syncPermissions([
            PanelPermissions::ViewAdmin->value,
            UserPermissions::All->value,
            DocumentPermissions::All->value,
            ImagePermissions::All->value,
            MediaPermissions::All->value,
            ActivityPermissions::All->value,
        ]);

        $roleUser->syncPermissions([
            PanelPermissions::ViewApp->value,
        ]);
    }
}

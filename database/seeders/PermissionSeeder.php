<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permissions\PermissionPermissions;
use App\Enums\Permissions\RolePermissions;
use App\Enums\Permissions\SystemPermissions;
use App\Enums\Permissions\UserPermissions;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

final class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // reset cached roles and permissions
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionsBag = [
            ...SystemPermissions::cases(),
            ...UserPermissions::cases(),
            ...RolePermissions::cases(),
            ...PermissionPermissions::cases(),
        ];

        foreach ($permissionsBag as $permission) {
            Permission::firstOrCreate(['name' => $permission->value]);
        }
    }
}

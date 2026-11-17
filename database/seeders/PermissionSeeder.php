<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

final class PermissionSeeder extends Seeder
{
    /**
     * Seed all permissions declared as enums.
     */
    public function run(): void
    {
        Artisan::call('mpac:permissions:sync');
    }
}

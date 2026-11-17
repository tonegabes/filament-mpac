<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use RuntimeException;

final class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('DevelopmentSeeder cannot be executed in production.');
        }

        $this->call([
            UserSeeder::class,
        ]);
    }
}

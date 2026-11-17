<?php

declare(strict_types=1);

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        activity()->disableLogging();

        $this->call([
            ProductionSeeder::class,
        ]);

        if (! app()->isProduction()) {
            $this->call([
                DevelopmentSeeder::class,
            ]);
        }

        activity()->enableLogging();
    }
}

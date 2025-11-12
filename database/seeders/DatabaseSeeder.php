<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Database\Seeders\OrderStatusSeeder as SeedersOrderStatusSeeder;
use Illuminate\Database\Seeder;
use OrderStatusSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminSeeder::class);
        $this->call(SeedersOrderStatusSeeder::class);
    }
}

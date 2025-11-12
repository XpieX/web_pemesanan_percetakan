<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('order_statuses')->insert([
            ['name' => 'Pending', 'label_color' => '#FFA500'],
            ['name' => 'Proses', 'label_color' => '#2196F3'],
            ['name' => 'Selesai', 'label_color' => '#4CAF50'],
            ['name' => 'Batal', 'label_color' => '#F44336'],
        ]);
    }
}

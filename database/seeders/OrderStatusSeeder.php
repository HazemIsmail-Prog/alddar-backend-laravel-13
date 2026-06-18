<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['id' => 1, 'name' => 'غير معين', 'color' => '#3b82f6'],
            ['id' => 2, 'name' => 'متوقف مؤقتا', 'color' => '#f59e0b'],
            ['id' => 3, 'name' => 'معين', 'color' => '#8b5cf6'],
            ['id' => 4, 'name' => 'مستلم', 'color' => '#06b6d4'],
            ['id' => 5, 'name' => 'وصول', 'color' => '#10b981'],
            ['id' => 6, 'name' => 'منفذ', 'color' => '#22c55e'],
            ['id' => 7, 'name' => 'ملغي', 'color' => '#ef4444'],
        ];

        OrderStatus::insert($statuses);
    }
}

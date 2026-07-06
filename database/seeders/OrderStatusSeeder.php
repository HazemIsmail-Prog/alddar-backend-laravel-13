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
            ['id' => 1, 'name' => 'غير معين', 'color' => '#3399ff'],
            ['id' => 2, 'name' => 'متوقف مؤقتا', 'color' => '#636f83'],
            ['id' => 3, 'name' => 'معين', 'color' => '#9b082d'],
            ['id' => 4, 'name' => 'مستلم', 'color' => '#d6b300'],
            ['id' => 5, 'name' => 'وصول', 'color' => '#e356e6'],
            ['id' => 6, 'name' => 'منفذ', 'color' => '#2eb85c'],
            ['id' => 7, 'name' => 'ملغي', 'color' => '#e55353'],
        ];

        OrderStatus::insert($statuses);
    }
}

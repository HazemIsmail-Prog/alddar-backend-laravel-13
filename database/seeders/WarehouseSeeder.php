<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = [
            [
                'name_en' => 'Main Warehouse',
                'name_ar' => 'المخزن الرئيسي',
                'type' => 'standard',
                'is_active' => true,
                'is_default' => true,
                'created_by' => 1,
            ],
            [
                'name_en' => 'Technician Warehouse 1',
                'name_ar' => 'مخزن الفني 1',
                'type' => 'standard',
                'is_active' => true,
                'is_default' => false,
                'created_by' => 1,
            ],
            [
                'name_en' => 'Technician Warehouse 2',
                'name_ar' => 'مخزن الفني 2',
                'type' => 'standard',
                'is_active' => true,
                'is_default' => false,
                'created_by' => 1,
            ],
        ];

        Warehouse::insert($warehouses);
    }
}

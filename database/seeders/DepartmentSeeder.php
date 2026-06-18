<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name_en' => 'Administration',
                'name_ar' => 'الادارة',
                'is_service_department' => false,
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'name_en' => 'Air Conditioning',
                'name_ar' => 'التكييف',
                'is_service_department' => true,
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'name_en' => 'Electrical',
                'name_ar' => 'الكهرباء',
                'is_service_department' => true,
                'is_active' => true,
                'created_by' => 1,
            ],
        ];

        Department::insert($departments);
    }
}

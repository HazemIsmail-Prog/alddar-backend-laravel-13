<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'parent_id' => null,
                'name_en' => 'Tools',
                'name_ar' => 'عدة',
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'parent_id' => null,
                'name_en' => 'Services',
                'name_ar' => 'خدمات',
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'parent_id' => null,
                'name_en' => 'Spare Parts',
                'name_ar' => 'قطع غيار',
                'is_active' => true,
                'created_by' => 1,
            ],
        ];

        Category::insert($categories);
    }
}

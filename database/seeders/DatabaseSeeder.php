<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name_en' => 'Test User',
            'name_ar' => 'مستخدم تجريبي',
            // 'email' => 'test@example.com',
            'civil_id' => '282102800373',
        ]);

        User::factory(10)->create();

        $this->call([
            DepartmentSeeder::class,
            PermissionSeeder::class,
            WarehouseSeeder::class,
            CategorySeeder::class,
            // ProductSeeder::class,
            PartySeeder::class,
            OrderStatusSeeder::class,
            ChartOfAccountSeeder::class,
        ]);

        $superAdminRole = Role::create([
            'name_en' => 'Super Admin',
            'name_ar' => 'المسؤول العام',
        ]);

        $superAdminRole->permissions()->sync(Permission::pluck('id')->toArray());

        $user = User::find(1);
        $user->roles()->sync([$superAdminRole->id]);
    }
}

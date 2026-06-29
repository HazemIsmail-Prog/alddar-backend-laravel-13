<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {


        $this->call([
            UserSeeder::class,
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

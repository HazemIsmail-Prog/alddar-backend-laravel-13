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
        User::factory()->create([
            'name_en' => 'Hazem Ismail',
            'name_ar' => 'حازم اسماعيل',
            'civil_id' => '282102800373',
            'password' => Hash::make('282102800373'),
        ]);

        User::factory()->create([
            'name_en' => 'Takieldin Samir Mohamed Badrelazab',
            'name_ar' => 'تقي الدين سمير محمد بدر العزب',
            'civil_id' => '294031103172',
            'password' => Hash::make('294031103172'),
        ]);

        // User::factory(10)->create();

        $this->call([
            DepartmentSeeder::class,
            PermissionSeeder::class,
            WarehouseSeeder::class,
            CategorySeeder::class,
            // ProductSeeder::class,
            // PartySeeder::class,
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

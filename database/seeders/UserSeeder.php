<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            array(
                'name_en' => 'Hazem Ismail',
                'name_ar' => 'حازم اسماعيل',
                'civil_id' => '282102800373',
                'password' => Hash::make('282102800373'),
                'is_technician' => false,
            ),
            array(
                'name_en' => 'Takieldin Samir Mohamed Badrelazab',
                'name_ar' => 'تقي الدين سمير محمد بدر العزب',
                'civil_id' => '294031103172',
                'password' => Hash::make('294031103172'),
                'is_technician' => false,
            ),
            array(
                'name_en' => 'AHMED YOUSEF NASEF ABOUASY',
                'name_ar' => 'احمد يوسف ناصف ابو عاصي',
                'civil_id' => '296101503286',
                'password' => Hash::make('296101503286'),
                'is_technician' => true,
            ),
            array(
                'name_en' => 'MOHAMMAD NASIM MOHAMMAD ANSARI',
                'name_ar' => 'محمد نسيم الصاري',
                'civil_id' => '292010602619',
                'password' => Hash::make('292010602619'),
                'is_technician' => true,
            ),
            array(
                'name_en' => 'EMAD ZAGHLOUL MOHAMED SHEHATA',
                'name_ar' => 'عماد زغلول محمد شحاته',
                'civil_id' => '282122506606',
                'password' => Hash::make('282122506606'),
                'is_technician' => true,
            ),
            array(
                'name_en' => 'MAHFOUZ KHALAFALLA ELABD WAZIRI',
                'name_ar' => 'محفوظ خلف الله العبد وزيري',
                'civil_id' => '290050106063',
                'password' => Hash::make('290050106063'),
                'is_technician' => true,
            )
        ];

        User::insert($users);

    }
}

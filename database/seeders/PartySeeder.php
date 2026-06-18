<?php

namespace Database\Seeders;

use App\Models\Party;
use Illuminate\Database\Seeder; 
use App\Models\Phone;
use App\Models\Location;
class PartySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        $parties = Party::factory(10)
            ->has(Phone::factory()->count(2))
            ->has(Location::factory()->count(2))
            ->create();

    }
}

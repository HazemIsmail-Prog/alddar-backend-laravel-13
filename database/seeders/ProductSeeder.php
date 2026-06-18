<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'sku' => 'AC-SPLIT-18000',
                'name' => 'Split Air Conditioner 18000 BTU',
                'product_type' => 'finished_good',
                'unit_of_measure' => 'piece',
                'is_active' => true,
                'is_purchasable' => true,
                'is_sellable' => true,
                'track_inventory' => true,
            ],
            [
                'sku' => 'AC-WIN-24000',
                'name' => 'Window Air Conditioner 24000 BTU',
                'product_type' => 'finished_good',
                'unit_of_measure' => 'piece',
                'is_active' => true,
                'is_purchasable' => true,
                'is_sellable' => true,
                'track_inventory' => true,
            ],
            [
                'sku' => 'FILTER-AC',
                'name' => 'Air Conditioner Filter',
                'product_type' => 'raw_material',
                'unit_of_measure' => 'piece',
                'is_active' => true,
                'is_purchasable' => true,
                'is_sellable' => true,
                'track_inventory' => true,
            ],
            [
                'sku' => 'GAS-R410A-10KG',
                'name' => 'Refrigerant Gas R410A - 10kg Cylinder',
                'product_type' => 'raw_material',
                // changed from 'cylinder' to 'kg' to match allowed values
                'unit_of_measure' => 'kg',
                'is_active' => true,
                'is_purchasable' => true,
                'is_sellable' => true,
                'track_inventory' => true,
            ],
            [
                'sku' => 'SERV-MAINT-AC',
                'name' => 'AC Preventive Maintenance Service',
                'product_type' => 'service',
                // changed from 'visit' to 'set' to match allowed values
                'unit_of_measure' => 'set',
                'is_active' => true,
                'is_purchasable' => false,
                'is_sellable' => true,
                'track_inventory' => false,
            ],
            [
                'sku' => 'SERV-REPAIR-AC',
                'name' => 'AC Repair Service',
                'product_type' => 'service',
                // changed from 'job' to 'set' to match allowed values
                'unit_of_measure' => 'set',
                'is_active' => true,
                'is_purchasable' => false,
                'is_sellable' => true,
                'track_inventory' => false,
            ],
            [
                'sku' => 'CAP-C-RUN-35UF',
                'name' => 'AC Run Capacitor 35uF',
                'product_type' => 'raw_material',
                'unit_of_measure' => 'piece',
                'is_active' => true,
                'is_purchasable' => true,
                'is_sellable' => true,
                'track_inventory' => true,
            ],
            [
                'sku' => 'THERMOSTAT-DIG',
                'name' => 'Digital Thermostat',
                'product_type' => 'raw_material',
                'unit_of_measure' => 'piece',
                'is_active' => true,
                'is_purchasable' => true,
                'is_sellable' => true,
                'track_inventory' => true,
            ],
            [
                'sku' => 'SERV-INSTALL-AC',
                'name' => 'AC Installation Service',
                'product_type' => 'service',
                // changed from 'job' to 'set' to match allowed values
                'unit_of_measure' => 'set',
                'is_active' => true,
                'is_purchasable' => false,
                'is_sellable' => true,
                'track_inventory' => false,
            ],
            [
                'sku' => 'DUCT-INSUL-24IN',
                'name' => 'Duct Insulation 24 Inch',
                'product_type' => 'raw_material',
                'unit_of_measure' => 'meter',
                'is_active' => true,
                'is_purchasable' => true,
                'is_sellable' => true,
                'track_inventory' => true,
            ],
        ];

        Product::insert($products);
    }
}

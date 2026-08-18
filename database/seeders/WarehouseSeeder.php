<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        // Explicit test warehouses linked to User 1
        $testWarehouses = [
            'Central Logistics Facility',
            'Northern Cold Storage',
            'East Distribution Hub',
        ];

        foreach ($testWarehouses as $name) {
            Facility::create([
                'address_id' => 1,
                'user_id' => 1,
                'facility_type' => 'warehouse',
                'facility_status' => 'approved',
                'business_type' => 'warehouse',
                'facility_name_en' => $name,
            ]);
        }

        // Additional generated warehouses
        Facility::factory()->warehouse()->count(10)->create();
    }
}

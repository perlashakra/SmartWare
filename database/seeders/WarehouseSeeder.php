<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        Facility::create([
            'address_id' => 1,
            'user_id' => 18,
            'facility_type' => 'warehouse',
            'facility_status' => 'approved',
            'business_type' => 'warehouse',
        ]);

        Facility::factory()->warehouse()->count(10)->create();
    }
}

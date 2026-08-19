<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        // Explicit test businesses linked to User 18
        Facility::create([
            'address_id' => 1,
            'user_id' => 18,
            'facility_type' => 'business',
            'facility_status' => 'approved',
            'business_type' => 'supermarket',
            'facility_name_en' => 'Primary Test Company',
        ]);

        Facility::create([
            'address_id' => 1,
            'user_id' => 18,
            'facility_type' => 'business',
            'facility_status' => 'approved',
            'business_type' => 'restaurant',
            'facility_name_en' => 'Secondary Test Enterprise',
        ]);

        // Additional generated businesses
        Facility::factory()->business()->count(10)->create();
    }
}

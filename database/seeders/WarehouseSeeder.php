<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Facility::create([
            'address_id' => 1,
            'user_id' => 1,
            'facility_name' => 'Abu Al Noor',
            'facility_type' => 'warehouse',
            'facility_status' => 'approved',
        ]);
    }
}

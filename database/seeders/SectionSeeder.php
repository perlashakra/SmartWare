<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = Facility::where('user_id', 1)
            ->where('facility_type', 'warehouse')
            ->get();

        $company = Facility::where('user_id', 18)
            ->where('facility_type', 'business')
            ->first();

        foreach ($warehouses as $warehouse) {
            // Main Storage Zone (Parent)
            $parentSection = Section::create([
                'warehouse_id' => $warehouse->id,
                'company_id' => $company?->id,
                'parent_id' => null,
                'name' => 'Zone A - Main Hold',
                'capacity' => 1000,
            ]);

            // Sub-sections (Children)
            Section::create([
                'warehouse_id' => $warehouse->id,
                'company_id' => $company?->id,
                'parent_id' => $parentSection->id,
                'name' => 'Rack A1 - General',
                'capacity' => 250,
            ]);

            Section::create([
                'warehouse_id' => $warehouse->id,
                'company_id' => $company?->id,
                'parent_id' => $parentSection->id,
                'name' => 'Rack A2 - High Value',
                'capacity' => 100,
            ]);
        }
    }
}

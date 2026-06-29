<?php

namespace Database\Seeders;

use App\Models\EmployeeAnnouncement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeAnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EmployeeAnnouncement::create([
            'employmentWarehouse_id' => 1,
            'manager_id' => 1,
            'first_name' => 'Anthony',
            'last_name' => 'Torbey',
            'national_id' => '10130017201',
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([ManagerSeeder::class]);
        $this->call([SuperAdminSeeder::class]);
        $this->call([ClientSeeder::class]);
        $this->call([AddressSeeder::class]);
        $this->call([CategorySeeder::class]);
        $this->call([CompanySeeder::class]);
        $this->call([ProductSeeder::class]);
        $this->call([BusinessSeeder::class]);
        $this->call([WarehouseSeeder::class]);
        $this->call([EmployeeAnnouncementSeeder::class]);
    }
}

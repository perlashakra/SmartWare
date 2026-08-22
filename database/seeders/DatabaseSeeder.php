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
        $this->call([ProductSeeder::class]);
        $this->call([ProductCategorySeeder::class]);
        $this->call([BusinessSeeder::class]);
        $this->call([WarehouseSeeder::class]);
        $this->call([SectionSeeder::class]);
        $this->call([InventorySeeder::class]);
        $this->call([DiscountSeeder::class]);
        $this->call([EmployeeAnnouncementSeeder::class]);
        $this->call([PendingRegistrationSeeder::class,]);
        //$this->call([WarehouseAnalyticsSeeder::class]);//this seeder is used alone
    }
}

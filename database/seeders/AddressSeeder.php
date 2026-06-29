<?php

namespace Database\Seeders;

use App\Models\Address;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Address::create([
            'name' => 'Mezzeh',
            'country' => 'Syria',
            'city' => 'Damascus',
            'street' => 'Fayez Mansour',
            'postal_code' => '70001',
            'latitude' => '36.267696',
            'longitude' => '33.508627',
        ]);
    }
}

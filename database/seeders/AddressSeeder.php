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

        Address::create([
            'name' => 'Mashroo3 Dummar',
            'country' => 'Syria',
            'city' => 'Damascus',
            'street' => 'Mashroo3 Dummar',
            'postal_code' => '45566',
            'latitude' => '36.267696',
            'longitude' => '33.508627',
        ]);

        Address::create([
            'name' => 'Sahnaya',
            'country' => 'Syria',
            'city' => 'Damascus',
            'street' => 'Sahnaya',
            'postal_code' => '45000',
            'latitude' => '36.267696',
            'longitude' => '33.508627',
        ]);

        Address::create([
            'name' => 'Adra',
            'country' => 'Syria',
            'city' => 'Damascus',
            'street' => 'Adra',
            'postal_code' => '20002',
            'latitude' => '36.267696',
            'longitude' => '33.508627',
        ]);
    }
}

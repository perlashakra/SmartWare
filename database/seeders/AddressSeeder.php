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
            'name' => 'Fayez Mansour, Mezzeh, Damascus, Syria',
            'latitude' => '36.267696',
            'longitude' => '33.508627',
        ]);

        Address::create([
            'name' => 'Mashroo3 Dummar, Damascus, Syria',
            'latitude' => '36.267696',
            'longitude' => '33.508627',
        ]);

        Address::create([
            'name' => 'Sahnaya, Damascus, Syria',
            'latitude' => '36.267696',
            'longitude' => '33.508627',
        ]);

        Address::create([
            'name' => 'Adra, Damascus, Syria',
            'latitude' => '36.267696',
            'longitude' => '33.508627',
        ]);
    }
}

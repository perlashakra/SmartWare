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
            'address' => 'Fayez Mansour, Mezzeh, Damascus, Syria',
            'latitude' => '36.267696',
            'longitude' => '33.508627',
        ]);

        Address::create([
            'address' => 'Mashroo3 Dummar, Damascus, Syria',
            'latitude' => '36.267696',
            'longitude' => '33.508627',
        ]);

        Address::create([
            'address' => 'Sahnaya, Damascus, Syria',
            'latitude' => '36.267696',
            'longitude' => '33.508627',
        ]);

        Address::create([
            'address' => 'Adra, Damascus, Syria',
            'latitude' => '36.267696',
            'longitude' => '33.508627',
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'first_name' => 'Joelle',
            'last_name' => 'Benyamin',
            'email' => 'benyaminjoelle6@gmail.com',
            'phone_number' => '0968691004',
            'password' => Hash::make('0123456789'),
            'role' => 'warehouse_admin',
        ]);
    }
}

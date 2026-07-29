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
            'first_name' => 'Nicole',
            'last_name' => 'Ekkeh',
            'email' => 'nicole.ekkeh@gmail.com',
            'phone_number' => '0936684934',
            'password' => Hash::make('0123456789'),
            'role' => 'warehouse_admin',
        ]);

        User::factory()->warehouse_admin()->count(10)->create();
    }
}

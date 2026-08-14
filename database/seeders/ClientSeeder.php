<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'first_name' => 'Perla',
            'last_name' => 'Shakra',
            'email' => 'perla.shakra@gmail.com',
            'phone_number' => '0993579666',
            'account_status' => 'approved',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('0123456789'),
            'role' => 'client',
        ]);

        User::factory()->client()->count(10)->create();
    }
}

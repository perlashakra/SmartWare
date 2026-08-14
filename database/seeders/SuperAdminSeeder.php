<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
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
            'account_status' => 'approved',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('0123456789'),
            'role' => 'super_admin',
        ]);

        User::factory()->super_admin()->count(5)->create();
    }
}

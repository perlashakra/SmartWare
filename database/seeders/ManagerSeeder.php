<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class ManagerSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'first_name' => 'Nicole',
            'last_name' => 'Ekkeh',
            'email' => 'nicole.ekkeh@gmail.com',
            'phone_number' => '0936684934',
            'account_status' => 'approved',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('0123456789'),
            'role' => 'warehouse_admin',
        ]);

        User::factory()->warehouse_admin()->count(10)->create();
    }
}

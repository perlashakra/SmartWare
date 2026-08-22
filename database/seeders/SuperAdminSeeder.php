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

        // Super admins are created directly (AdminController::createAdmin) and
        // are never subject to the pending-review flow, unlike warehouse_admins
        // and clients. 'onboarding_complete' is intentionally left out of
        // User::$fillable, so it must be set via a query-builder update rather
        // than User::create()/update() to avoid being silently dropped.
        User::where('role', 'super_admin')->update([
            'account_status' => 'approved',
            'onboarding_complete' => true,
        ]);
    }
}

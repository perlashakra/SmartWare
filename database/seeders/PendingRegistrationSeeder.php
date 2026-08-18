<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class PendingRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password123');
        $verifiedAt = Carbon::now();

        // 1. Users who completed onboarding steps but are pending admin approval (3 users)
        foreach (range(1, 3) as $i) {
            User::create([
                'first_name' => "CompletedOnboarding{$i}",
                'last_name' => 'User',
                'email' => "pending.onboarded{$i}@example.com",
                'phone_number' => "+1555010{$i}00",
                'email_verified_at' => $verifiedAt,
                'password' => $defaultPassword,
                'role' => 'warehouse_admin',
                'onboarding_complete' => true,
                'account_status' => 'pending',
                'language_preference' => 'en',
            ]);
        }

        // 2. Users who abandoned or have not finished onboarding yet (3 users)
        foreach (range(1, 3) as $i) {
            User::create([
                'first_name' => "IncompleteOnboarding{$i}",
                'last_name' => 'User',
                'email' => "pending.incomplete{$i}@example.com",
                'phone_number' => "+1555020{$i}00",
                'email_verified_at' => $verifiedAt,
                'password' => $defaultPassword,
                'role' => 'client',
                'onboarding_complete' => false,
                'account_status' => 'pending',
                'language_preference' => 'en',
            ]);
        }
    }
}

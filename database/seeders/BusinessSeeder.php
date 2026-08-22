<?php

namespace Database\Seeders;

use App\Enums\BusinessTypeEnum;
use App\Models\Address;
use App\Models\Category;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    /**
     * Same ownership/approval rules as WarehouseSeeder, applied to clients
     * and "business" type facilities instead of warehouse_admins/warehouses.
     */
    public function run(): void
    {
        $clients = User::where('role', 'client')->orderBy('id')->get();
        $addressIds = Address::orderBy('id')->pluck('id')->toArray();

        if ($clients->isEmpty() || empty($addressIds)) {
            $this->command->warn('No client users or addresses found. Skipping BusinessSeeder.');
            return;
        }

        $addrCursor = 0;
        $nextAddress = function () use ($addressIds, &$addrCursor) {
            $id = $addressIds[$addrCursor % count($addressIds)];
            $addrCursor++;
            return $id;
        };

        $businessTypes = BusinessTypeEnum::cases();
        $namesByType = [
            'restaurant' => ['The Olive Branch', 'Damascus Grill', 'Cedar Table', 'Levant Kitchen'],
            'pharmacy' => ['Wellness Pharmacy', 'CarePlus Pharmacy', 'Family Health Pharmacy'],
            'clothing_store' => ['Urban Thread', 'Style Corner', 'Fashion Loft'],
            'electronics_store' => ['ByteHub Electronics', 'CircuitZone', 'TechNest'],
            'supermarket' => ['FreshMart', 'Neighborhood Supermarket', 'GreenBasket'],
            'makeup_store' => ['Glow Beauty Bar', 'Luxe Cosmetics', 'Bloom Beauty'],
            'furniture_store' => ['Home & Timber Furniture', 'Comfort Living', 'Oakwood Interiors'],
        ];

        foreach ($clients as $index => $client) {
            if ($index === 0) {
                // Explicit scenario user (Perla): one approved storefront plus
                // a second one still awaiting admin review.
                $statuses = ['approved', 'pending'];
            } elseif ($index === 1) {
                // Explicit scenario user: single storefront, rejected on review.
                $statuses = ['rejected'];
            } else {
                $count = random_int(1, 10) <= 2 ? 2 : 1;
                $statuses = [];
                for ($i = 0; $i < $count; $i++) {
                    $statuses[] = $i < $count - 1
                        ? 'approved'
                        : collect(['approved', 'approved', 'pending', 'submitted', 'rejected'])->random();
                }
            }

            foreach ($statuses as $n => $status) {
                $type = $businessTypes[array_rand($businessTypes)];
                $poolNames = $namesByType[$type->value];

                $facility = Facility::create([
                    'address_id' => $nextAddress(),
                    'user_id' => $client->id,
                    'facility_type' => 'business',
                    'facility_status' => $status,
                    'business_type' => $type->value,
                    'facility_name_en' => $poolNames[array_rand($poolNames)],
                ]);

                // Mirror OnboardingController::savePreferences: a business only
                // picks categories that its BusinessTypeEnum allows.
                $allowedCategories = collect($type->categories())->map(fn ($c) => $c->value);
                $chosen = $allowedCategories->shuffle()->take(min(5, $allowedCategories->count()));
                $categoryIds = Category::whereIn('name', $chosen)->pluck('id');

                if ($categoryIds->isNotEmpty()) {
                    $facility->categories()->syncWithoutDetaching($categoryIds);
                }
            }

            $accountStatus = match ($statuses[0]) {
                'approved' => 'approved',
                'rejected' => 'deleted',
                default => 'pending',
            };

            User::where('id', $client->id)->update([
                'account_status' => $accountStatus,
                'onboarding_complete' => true,
            ]);
        }
    }
}

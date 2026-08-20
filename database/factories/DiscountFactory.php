<?php

namespace Database\Factories;

use App\Models\Discount;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    public function definition(): array
    {
        $starts_at = fake()->dateTimeBetween('-1 month', '+1 month');
        $ends_at = fake()->dateTimeBetween($starts_at, '+1 month');
        return [
            'inventory_id' => Inventory::query()->inRandomOrder()->value('id'),
            'created_by' => User::query()->inRandomOrder()->value('id'),
            'percentage' => fake()->randomFloat(2, 1, 50),
            'starts_at' => $starts_at,
            'ends_at' => $ends_at,
            'is_active' => fake()->boolean(80),
        ];
    }
}

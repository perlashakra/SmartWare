<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 20) as $i) {
            $product = Product::factory()->create();

            $product->categories()->attach(
                Category::inRandomOrder()->limit(fake()->numberBetween(1, 3))->pluck('id')
            );
        }
    }
}

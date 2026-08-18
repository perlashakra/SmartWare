<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Section;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $sections = Section::whereHas('warehouse', function ($query) {
            $query->where('user_id', 1);
        })->get();

        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->warn('No products found. Skipping InventorySeeder.');
            return;
        }

        foreach ($sections as $section) {
            // Pick 2-4 distinct random products for each section
            $assignedProducts = $products->random(min(rand(2, 4), $products->count()));

            foreach ($assignedProducts as $product) {
                Inventory::updateOrCreate(
                    [
                        'section_id' => $section->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'quantity' => rand(10, 200),
                        'unit_price' => $product->unit_price ?? rand(100, 1500) / 10,
                    ]
                );
            }
        }
    }
}

<?php

namespace Database\Factories;

use App\Enums\ContainerType;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::inRandomOrder()->value('id'),
            'sku' => fake()->unique()->bothify('???###??##'),
            'name_en' => fake()->word(),
            'name_ar' => fake('ar_SA')->word(),
            'price' => fake()->numberBetween(0, 99999999),
            'container_type' => fake()->randomElement(ContainerType::cases())->value,
            'product_image' => fake()->image(),
        ];
    }
}
 
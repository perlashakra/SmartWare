<?php

namespace Database\Factories;

use App\Enums\ContainerType;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use UnitEnum;

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
            'unit' => fake()->randomElement(UnitEnum::cases())->value,
            'product_image' => fake()->image(),
            'description_en' => fake()->text(200),
            'description_ar' => fake('ar_SA')->text(200),
        ];
    }
}
 
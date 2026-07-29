<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyFactory extends Factory
{
    public function definition(): array
    {
        $company = fake()->company();
        return [
            'address_id' => Address::inRandomOrder()->value('id'),
            'name' => $company,
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'website' => 'https://'.Str::slug($company).'.com',
        ];
    }
}

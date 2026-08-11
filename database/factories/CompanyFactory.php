<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyFactory extends Factory
{
    public function definition(): array
    {
        $company_en = fake()->company();
        $company_ar = fake('ar_SA')->company();

        return [
            'address_id' => Address::inRandomOrder()->value('id'),
            'name_en' => $company_en,
            'name_ar' => $company_ar,
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'website' => 'https://'.Str::slug($company_en).'.com',
        ];
    }
}

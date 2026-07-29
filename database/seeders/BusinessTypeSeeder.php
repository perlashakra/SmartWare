<?php

namespace Database\Seeders;

use App\Enums\BusinessTypeEnum;
use App\Models\BusinessType;
use Illuminate\Database\Seeder;

class BusinessTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach(BusinessTypeEnum::cases() as $businessType){
            BusinessType::updateOrCreate([
                'business_type' => $businessType->value,
            ]);
        }
    }
}

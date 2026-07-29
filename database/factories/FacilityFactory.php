<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacilityFactory extends Factory
{
    protected $model = Facility::class;
    public function definition(): array
    {
        return [
            'address_id' => Address::inRandomOrder()->value('id'),
            'facility_name' => fake()->company(),
            'facility_status' => 'approved',
        ];
    }

    public function business():static
    {
        return $this->state(fn() => [
            'user_id' => User::where('role', 'client')->inRandomOrder()->value('id'),
            'facility_type' => 'business',
        ]);
    }

    public function warehouse():static
    {
        return $this->state(fn() => [
            'user_id' => User::where('role', 'warehouse_admin')->inRandomOrder()->value('id'),
            'facility_type' => 'warehouse',
        ]);
    }
}

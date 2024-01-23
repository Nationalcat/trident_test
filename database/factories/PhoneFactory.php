<?php

namespace Database\Factories;

use App\Models\Phone;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhoneFactory extends Factory
{
    protected $model = Phone::class;

    public function definition(): array
    {
        return [
            'phone' => $this->faker->numberBetween(1, 10000),
            'is_blacklisted' => false,
        ];
    }
}

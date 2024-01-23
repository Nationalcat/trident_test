<?php

namespace Database\Factories;

use App\Models\Phone;
use App\Models\Queue;
use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class QueueFactory extends Factory
{
    protected $model = Queue::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->numberBetween(1, 1000),
            'number' => $this->faker->randomNumber(),
            'is_activated' => true,
            'is_online' => true,
            'booked_at' => Carbon::now(),
            'check_in_at' => null,
            'check_out_at' => null,
            'seat' => 2,
            'phone_id' => Phone::factory(),
            'table_id' => Table::factory(),
        ];
    }
}

<?php

namespace Database\Seeders;

use App\Models\Queue;
use App\Models\Table;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class QueueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tables = Table::get();
        Queue::factory()->state([
            'is_activated' => true,
            'is_online' => true,
            'seat' => $tables->random()->seat,
            'number' => Queue::max('number') + 1,
            'booked_at' => Carbon::now(),
            'table_id' => null,
        ])->create();
    }
}

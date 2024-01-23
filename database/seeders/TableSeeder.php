<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Table::create(['seat' => 2, 'is_activated' => true]);
        Table::create(['seat' => 4, 'is_activated' => true]);
        Table::create(['seat' => 6, 'is_activated' => true]);
    }
}

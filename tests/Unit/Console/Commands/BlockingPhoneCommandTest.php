<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\BlockingPhoneCommand;
use App\Models\Phone;
use App\Models\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockingPhoneCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group BlockingPhoneCommand
     * @author 56
     * @description
     */
    public function block_phone_is_work(): void
    {
        ## arrange
        // 有到
        Phone::factory()
            ->has(Queue::factory()->count(3)->sequence([
                'number' => 1,
                'check_in_at' => now(),
            ], [
                'number' => 2,
                'check_in_at' => now(),
            ], [
                'number' => 3,
                'check_in_at' => now(),
            ]))
            ->state(['id' => 1])
            ->create();
        // 三次
        Phone::factory()
            ->has(Queue::factory()->count(3)->sequence([
                'number' => 4,
                'booked_at'=> now()->subSecond(),
                'check_in_at' => null,
            ], [
                'number' => 5,
                'booked_at'=> now()->subSecond(),
                'check_in_at' => null,
            ], [
                'number' => 6,
                'booked_at'=> now()->subSecond(),
                'check_in_at' => null,
            ]))
            ->state(['id' => 2])
            ->create();
        // 未到二次
        Phone::factory()
            ->has(Queue::factory()->count(2)->sequence([
                'number' => 7,
                'booked_at'=> now()->subSecond(),
                'check_in_at' => null,
            ], [
                'number' => 8,
                'booked_at'=> now()->subSecond(),
                'check_in_at' => null,
            ]))
            ->state(['id' => 3])
            ->create();
        ## act
        $this->artisan(BlockingPhoneCommand::class);
        ## assert
        $this->assertDatabaseHas(Phone::class, [
            'id' => 1,
            'is_blacklisted' => false,
        ]);
        $this->assertDatabaseHas(Phone::class, [
            'id' => 2,
            'is_blacklisted' => true,
        ]);
        $this->assertDatabaseHas(Phone::class, [
            'id' => 3,
            'is_blacklisted' => false,
        ]);
    }
}

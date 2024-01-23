<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueScopeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group QueueScope
     * @author 56
     * @description
     */
    public function get_in_queued_is_work(): void
    {
        ## arrange
        Queue::factory()->create([
            'id' => 1,
            'number' => 1,
            'booked_at' => now(),
            'check_in_at' => null,
        ]);
        // 昨天
        Queue::factory()->create([
            'id' => 2,
            'number' => 2,
            'booked_at' => now()->subDay(),
            'check_in_at' => null,
        ]);
        // 已入場
        Queue::factory()->create([
            'id' => 3,
            'number' => 3,
            'booked_at' => now(),
            'check_in_at' => now(),
        ]);
        // 已逾時
        Queue::factory()->create([
            'id' => 4,
            'number' => 4,
            'booked_at' => now()->addSecond(),
            'check_in_at' => null,
        ]);
        ## act
        $result = Queue::inQueued()->get();
        ## assert
        self::assertSame(1, $result->first()->id);
        self::assertSame(1, $result->count());
    }
}

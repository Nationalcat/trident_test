<?php

namespace Tests\Unit\Filters\Staff;

use App\Filters\Staff\QueueFilter;
use App\Models\Phone;
use App\Models\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueFilterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var QueueFilter|mixed
     */
    private QueueFilter $target;

    public function setUp(): void
    {
        parent::setUp();
        $this->target = $this->app->make(QueueFilter::class);
    }

    /**
     * @test
     * @group QueueFilter
     * @author 56
     */
    public function search_date_is_work(): void
    {
        ## arrange
        Queue::factory()
            ->state(['id' => 222, 'booked_at' => '2024-01-24 12:00:00'])
            ->create();
        Queue::factory()
            ->state(['id' => 333, 'booked_at' => '2024-01-25 12:00:00'])
            ->create();
        ## act
        $result = $this->target->filterByDecorators(Queue::query(), [
            'date' => '2024-01-25',
        ])->first();
        ## assert
        self::assertSame(333, $result->id);
    }

    /**
     * @test
     * @group QueueFilter
     * @author 56
     */
    public function search_phone_is_work(): void
    {
        ## arrange
        Queue::factory()
            ->for(Phone::factory()->state([
                'phone' => '0912345678',
            ]))
            ->state(['id' => 222])
            ->create();
        Queue::factory()
            ->for(Phone::factory()->state([
                'phone' => '0912123123',
            ]))
            ->state(['id' => 333])
            ->create();
        ## act
        $result = $this->target->filterByDecorators(Queue::query(), [
            'phone' => '0912123123',
        ])->first();
        ## assert
        self::assertSame(333, $result->id);
    }
}

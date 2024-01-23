<?php

namespace Tests\Unit\Http\Frontend;

use App\Models\Phone;
use App\Models\Queue;
use App\Models\Table;
use App\Supports\Facades\ID;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class QueueControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group QueueController
     * @author 56
     * @description
     */
    public function get_queues_is_work(): void
    {
        ## arrange
        Carbon::setTestNow('2024-01-23 12:00:00');
        Table::factory()->create(['id' => 1, 'seat' => 2]);
        Table::factory()->create(['id' => 2, 'seat' => 2, 'is_activated' => false]);
        Table::factory()->create(['id' => 3, 'seat' => 4]);
        Queue::factory()->state([
            'table_id' => null,
            'seat' => 2,
            'number' => 30,
            'booked_at' => '2024-01-23 12:00:00',
            'check_in_at' => null,
        ])->create();
        Queue::factory()->state([
            'table_id' => null,
            'seat' => 2,
            'number' => 99,
            'booked_at' => '2024-01-23 12:00:00',
            'check_in_at' => null,
        ])->create();
        Queue::factory()->state([
            'table_id' => null,
            'seat' => 4,
            'number' => 992,
            'booked_at' => '2024-01-23 12:00:00',
            'check_in_at' => null,
        ])->create();
        ## act
        $response = $this->getJson(route('api.queues.index'));
        ## assert
        $response->assertSuccessful();
        self::assertSame([
            [
                'seat' => 2,
                'current_number' => 30,
                'latest_number' => 99,
                'queue_count' => 2,
            ],
            [
                'seat' => 4,
                'current_number' => 992,
                'latest_number' => 992,
                'queue_count' => 1,
            ],
        ], $response->json());
    }

    /**
     * @test
     * @group QueueController
     * @author 56
     * @description
     */
    public function get_queues_is_work_with_invalid_queues(): void
    {
        ## arrange
        Carbon::setTestNow('2024-01-23 12:00:00');
        Table::factory()->create(['id' => 1, 'seat' => 2, 'is_activated' => false]);
        Table::factory()->create(['id' => 2, 'seat' => 2]);
        Table::factory()->create(['id' => 3, 'seat' => 4]);
        // 還沒到訂位時間
        Queue::factory()->state([
            'table_id' => null,
            'number' => 111,
            'seat' => 2,
            'booked_at' => '2024-01-23 13:00:00',
            'check_in_at' => null,
        ])->create();
        // 已入座
        Queue::factory()->state([
            'table_id' => 2,
            'seat' => 2,
            'number' => 99,
            'booked_at' => '2024-01-23 11:00:00',
            'check_in_at' => '2024-01-23 11:30:00',
        ])->create();
        ## act
        $response = $this->getJson(route('api.queues.index'));
        ## assert
        $response->assertSuccessful();
        self::assertSame([
            [
                'seat' => 2,
                'current_number' => null,
                'latest_number' => null,
                'queue_count' => 0,
            ],
            [
                'seat' => 4,
                'current_number' => null,
                'latest_number' => null,
                'queue_count' => 0,
            ],
        ], $response->json());
    }

    /**
     * @test
     * @group QueueController
     * @author 56
     * @description
     */
    public function get_queue_is_work(): void
    {
        ## arrange
        Carbon::setTestNow('2024-01-23 12:00:00');
        Table::factory()->state(['id' => 1, 'seat' => 1])->create();
        Table::factory()->state(['id' => 2, 'seat' => 2])->create();
        Table::factory()->state(['id' => 3, 'seat' => 4])->create();
        Queue::factory()->state([
            'table_id' => null,
            'id' => 5,
            'seat' => 2,
            'number' => 20,
            'booked_at' => '2024-01-23 11:00:00',
            'check_out_at' => null,
        ])->create();
        Queue::factory()->state([
            'table_id' => null,
            'id' => 22,
            'seat' => 4,
            'number' => 123,
            'booked_at' => '2024-01-23 11:23:00',
            'check_out_at' => null,
        ])->create();
        Queue::factory()->state([
            'table_id' => null,
            'id' => 50,
            'seat' => 2,
            'number' => 333,
            'booked_at' => '2024-01-23 11:30:00',
            'check_out_at' => null,
        ])->create();
        ## act
        $response = $this->getJson(route('api.queues.show', [
            'code' => ID::encode(50, 333, 2),
        ]));
        ## assert
        self::assertSame([
            'seat' => 2,
            'your_number' => 333,
            'current_number' => 20,
            'queue_count' => 1,
        ], $response->json());
    }

    /**
     * @test
     * @group QueueController
     * @author 56
     * @description
     */
    public function create_queue_is_work(): void
    {
        ## arrange
        Carbon::setTestNow('2024-01-23 12:00:00');
        Table::factory()->state(['id' => 1, 'seat' => 2])->create();
        Queue::factory()->state([
            'table_id' => null,
            'id' => 8,
            'seat' => 2,
            'number' => 23,
            'booked_at' => '2024-01-23 11:00:00',
            'check_out_at' => null,
        ])->create();
        Queue::factory()->state([
            'table_id' => null,
            'id' => 5,
            'seat' => 2,
            'number' => 20,
            'booked_at' => '2024-01-23 11:00:00',
            'check_out_at' => null,
        ])->create();
        Phone::factory()->state([
            'id' => 123,
            'phone' => '0912345678',
            'is_blacklisted' => false,
        ])->create();
        ## act
        $response = $this->postJson(route('api.queues.store'), [
            'phone' => '0912345678',
            'name' => '柑仔',
            'seat' => 2,
        ]);
        ## assert
        $response->assertSuccessful();
        $this->assertDatabaseHas(Queue::class, [
            'phone_id' => 123,
            'name' => '柑仔',
            'seat' => 2,
            'number' => 24,
            'is_online' => true,
            'booked_at' => '2024-01-23 12:00:00',
            'check_in_at' => null,
            'check_out_at' => null,
        ]);
        self::assertSame([
            'seat' => 2,
            'your_number' => 24,
            'current_number' => 20,
            'queue_count' => 2,
        ], $response->json());
    }

    /**
     * @test
     * @group QueueController
     * @author 56
     * @description
     */
    public function create_queue_is_work_when_the_first_of_day(): void
    {
        ## arrange
        Carbon::setTestNow('2024-01-23 12:00:00');
        Table::factory()->state(['id' => 1, 'seat' => 2])->create();
        Queue::factory()->state([
            'table_id' => null,
            'id' => 8,
            'seat' => 2,
            'number' => 23,
            'booked_at' => '2024-01-22 11:00:00',
            'check_out_at' => null,
        ])->create();
        Phone::factory()->state([
            'id' => 123,
            'phone' => '0912345678',
            'is_blacklisted' => false,
        ])->create();
        ## act
        $response = $this->postJson(route('api.queues.store'), [
            'phone' => '0912345678',
            'name' => '柑仔',
            'seat' => 2,
        ]);
        ## assert
        $response->assertSuccessful();
        $this->assertDatabaseHas(Queue::class, [
            'phone_id' => 123,
            'name' => '柑仔',
            'seat' => 2,
            'number' => 1,
            'is_online' => true,
            'booked_at' => '2024-01-23 12:00:00',
            'check_in_at' => null,
            'check_out_at' => null,
        ]);
        self::assertSame([
            'seat' => 2,
            'your_number' => 1,
            'current_number' => null,
            'queue_count' => 0,
        ], $response->json());
    }
}

<?php

namespace Tests\Unit\Http\Staff;

use App\Models\Phone;
use App\Models\Queue;
use App\Models\Table;
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
        $response = $this->getJson(route('api.staff.queues.index'));
        ## assert
        $response->assertSuccessful();
        self::assertSame([
            'data' => [
                [
                    'created_at' => '2024-01-23 12:00:00',
                    'seat' => 2,
                    'queue_number' => 1,
                    'check_in_at' => null,
                    'check_out_at' => null,
                    'no_show' => false,
                    'booked_at' => '2024-01-23 12:00:00',
                ],
                [
                    'created_at' => '2024-01-23 12:00:00',
                    'seat' => 2,
                    'queue_number' => 2,
                    'check_in_at' => null,
                    'check_out_at' => null,
                    'no_show' => false,
                    'booked_at' => '2024-01-23 12:00:00',
                ],
                [
                    'created_at' => '2024-01-23 12:00:00',
                    'seat' => 4,
                    'queue_number' => 1,
                    'check_in_at' => null,
                    'check_out_at' => null,
                    'no_show' => false,
                    'booked_at' => '2024-01-23 12:00:00',
                ],
            ],
            'total' => 3,
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
        // 提早吃完
        Queue::factory()->state([
            'table_id' => 2,
            'seat' => 2,
            'number' => 99,
            'booked_at' => '2024-01-23 11:00:00',
            'check_in_at' => '2024-01-23 11:30:00',
        ])->create();
        // 爽約仔
        Queue::factory()->state([
            'table_id' => null,
            'number' => 125,
            'seat' => 2,
            'booked_at' => '2024-01-23 11:00:00',
            'check_in_at' => null,
        ])->create();
        ## act
        $response = $this->getJson(route('api.staff.queues.index'));
        ## assert
        $response->assertSuccessful();
        self::assertSame([
            'data' => [
                [
                    'created_at' => '2024-01-23 12:00:00',
                    'seat' => 2,
                    'queue_number' => 1,
                    'check_in_at' => null,
                    'check_out_at' => null,
                    'no_show' => false,
                    'booked_at' => '2024-01-23 13:00:00',
                ],
                [
                    'created_at' => '2024-01-23 12:00:00',
                    'seat' => 2,
                    'queue_number' => 2,
                    'check_in_at' => '2024-01-23 11:30:00',
                    'check_out_at' => null,
                    'no_show' => false,
                    'booked_at' => '2024-01-23 11:00:00',
                ],
                [
                    'created_at' => '2024-01-23 12:00:00',
                    'seat' => 2,
                    'queue_number' => 3,
                    'check_in_at' => null,
                    'check_out_at' => null,
                    'no_show' => true,
                    'booked_at' => '2024-01-23 11:00:00',
                ],
            ],
            'total' => 3,
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
        $response = $this->postJson(route('api.staff.queues.store'), [
            'phone' => '0912345678',
            'name' => '柑仔',
            'seat' => 2,
            'date' => '2024-01-23 13:00:00',
        ]);
        ## assert
        $response->assertSuccessful();
        $this->assertDatabaseHas(Queue::class, [
            'phone_id' => 123,
            'name' => '柑仔',
            'seat' => 2,
            'number' => 24,
            'is_online' => false,
            'booked_at' => '2024-01-23 13:00:00',
            'check_in_at' => null,
            'check_out_at' => null,
        ]);
        self::assertSame('http://trident_test.test/api/queues/4QRlCoHUSa', $response->json());
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
        $response = $this->postJson(route('api.staff.queues.store'), [
            'phone' => '0912345678',
            'name' => '柑仔',
            'seat' => 2,
            'date' => '2024-01-23 13:00:00',
        ]);
        ## assert
        $response->assertSuccessful();
        $this->assertDatabaseHas(Queue::class, [
            'phone_id' => 123,
            'name' => '柑仔',
            'seat' => 2,
            'number' => 1,
            'is_online' => false,
            'booked_at' => '2024-01-23 13:00:00',
            'check_in_at' => null,
            'check_out_at' => null,
        ]);
        self::assertSame('http://trident_test.test/api/queues/LqrHiUqBwG', $response->json());
    }

    /**
     * @test
     * @group QueueController
     * @author 56
     * @description
     */
    public function get_report_is_work(): void
    {
        ## arrange
        Queue::factory()->state([
            'is_online' => false,
            'booked_at' => '2024-01-23 12:00:00',
            'check_in_at' => '2024-01-23 12:00:00',
        ])->create();
        Queue::factory()->state([
            'is_online' => true,
            'booked_at' => '2024-01-23 11:00:00',
            'check_in_at' => '2024-01-23 12:00:00',
        ])->create();
        Queue::factory()->state([
            'is_online' => true,
            'booked_at' => '2024-01-23 11:30:00',
            'check_in_at' => '2024-01-23 12:00:00',
        ])->create();
        ## act
        $response = $this->getJson(route('api.staff.queues.report', ['date' => '2024-01-23']));
        ## assert
        $response->assertSuccessful();
        self::assertSame([
            'from_online' => 2,
            'from_site' => 1,
            'avg_wait_reports' => [
                [
                    'started_hour' => 11,
                    'avg_wait_time' => 2700,
                ],
                [
                    'started_hour' => 12,
                    'avg_wait_time' => 0,
                ],
            ],
        ], $response->json());
    }

    /**
     * @test
     * @group QueueController
     * @author 56
     * @description
     */
    public function check_in_is_work(): void
    {
        ## arrange
        Carbon::setTestNow('2024-01-23 12:00:00');
        Table::factory()->state(['id' => 33])->create();
        Queue::factory()
            ->for(Phone::factory()->state(['phone' => '0912345678']))
            ->state([
                'id' => 111,
                'table_id' => null,
                'check_in_at' => null,
            ])
            ->create();
        ## act
        $response = $this->putJson(route('api.staff.queues.check-in'), [
            'id' => 111,
            'table_id' => 33,
        ]);
        ## assert
        $response->assertSuccessful();
    }

    /**
     * @test
     * @group QueueController
     * @author 56
     * @description
     */
    public function check_in_is_not_work_when_the_seat_is_lack(): void
    {
        ## arrange
        Carbon::setTestNow('2024-01-23 12:00:00');
        Table::factory()->state(['id' => 33, 'seat' => 2])->create();
        Queue::factory()
            ->for(Phone::factory()->state(['phone' => '0912345678']))
            ->state([
                'id' => 222,
                'table_id' => null,
                'check_in_at' => null,
                'seat' => 3,
            ])
            ->create();
        ## act
        $response = $this->putJson(route('api.staff.queues.check-in'), [
            'id' => 222,
            'table_id' => 33,
        ]);
        ## assert
        $response->assertStatus(400);
        self::assertSame('座位數不足', $response->json());
    }

    /**
     * @test
     * @group QueueController
     * @author 56
     * @description
     */
    public function check_out_is_work(): void
    {
        ## arrange
        Carbon::setTestNow('2024-01-23 12:00:00');
        Queue::factory()
            ->for(Phone::factory()->state(['phone' => '0912345678']))
            ->for(Table::factory()->state(['id' => 33]))
            ->state([
                'id' => 321,
                'check_in_at' => '2024-01-23 11:00:00',
                'check_out_at' => null,
                'booked_at' => '2024-01-23 11:00:00',
            ])
            ->create();
        ## act
        $response = $this->putJson(route('api.staff.queues.check-out', ['id' => 33]));
        ## assert
        $response->assertSuccessful();
        self::assertSame('ok', $response->json());
        $this->assertDatabaseHas(Queue::class, [
            'id' => 321,
            'check_out_at' => '2024-01-23 12:00:00',
        ]);
    }
}

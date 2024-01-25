<?php

namespace Tests\Unit\Http\Staff;

use App\Models\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group TableController
     * @author 56
     * @description
     */
    public function get_tables_is_work(): void
    {
        ## arrange
        Table::factory()->state([
            'id' => 33,
            'seat' => 2,
            'is_activated' => true,
        ])->create();
        ## act
        $response = $this->getJson(route('api.staff.tables.index'));
        ## assert
        $response->assertSuccessful();
        self::assertSame([
            'data' => [
                [
                    'id' => 33,
                    'seat' => 2,
                    'is_activated' => true,
                ],
            ],
            'total' => 1,
        ], $response->json());
    }

    /**
     * @test
     * @group TableController
     * @author 56
     * @description
     */
    public function create_table_is_work(): void
    {
        ## arrange
        ## act
        $response = $this->postJson(route('api.staff.tables.store'), [
            'seat' => 2,
        ]);
        ## assert
        self::assertSame('ok', $response->json());
        $this->assertDatabaseHas(Table::class, [
            'seat' => 2,
        ]);
    }

    /**
     * @test
     * @group TableController
     * @author 56
     * @description
     */
    public function disable_table_is_work(): void
    {
        ## arrange
        Table::factory()->state([
            'id' => 33,
            'seat' => 2,
            'is_activated' => true,
        ])->create();
        ## act
        $response = $this->putJson(route('api.staff.tables.disable', [
            'id' => 33,
        ]));
        ## assert
        self::assertSame('ok', $response->json());
        $this->assertDatabaseHas(Table::class, [
            'id' => 33,
            'seat' => 2,
            'is_activated' => false,
        ]);
    }

    /**
     * @test
     * @group TableController
     * @author 56
     * @description
     */
    public function disable_table_is_not_work_when_id_is_not_found(): void
    {
        ## arrange
        ## act
        $response = $this->putJson(route('api.staff.tables.disable', [
            'id' => 35,
        ]));
        ## assert
        $response->assertNotFound();
    }
}

<?php

namespace Tests\Unit\Http\Staff;

use App\Models\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhoneControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group PhoneController2
     * @author 56
     * @description
     */
    public function get_phones_is_work(): void
    {
        ## arrange
        Phone::factory()->state([
            'id' => 3123,
            'phone' => '0912345678',
            'is_blacklisted' => true,
        ])->create();
        ## act
        $response = $this->getJson(route('api.staff.phones.index'));
        ## assert
        $response->assertSuccessful();
        $response->assertJson([
            'data' => [
                [
                    'id' => 3123,
                    'phone' => '0912345678',
                    'is_blacklisted' => true,
                ],
            ],
            'total' => 1,
        ]);
    }

    /**
     * @test
     * @group PhoneController2
     * @author 56
     * @description
     */
    public function block_phones_is_work(): void
    {
        ## arrange
        Phone::factory()->state([
            'id' => 3123,
            'phone' => '0912345678',
            'is_blacklisted' => false,
        ])->create();
        ## act
        $response = $this->putJson(route('api.staff.phones.block-phones'), [
            'ids' => [3123],
        ]);
        ## assert
        $response->assertSuccessful();
        self::assertSame('ok', $response->json());
        $this->assertDatabaseHas(Phone::class, [
            'id' => 3123,
            'phone' => '0912345678',
            'is_blacklisted' => true,
        ]);
    }

    /**
     * @test
     * @group PhoneController2
     * @author 56
     * @description
     */
    public function unblock_phones_is_work(): void
    {
        ## arrange
        Phone::factory()->state([
            'id' => 3123,
            'phone' => '0912345678',
            'is_blacklisted' => true,
        ])->create();
        ## act
        $response = $this->putJson(route('api.staff.phones.unblock-phones'), [
            'ids' => [3123],
        ]);
        ## assert
        $response->assertSuccessful();
        self::assertSame('ok', $response->json());
        $this->assertDatabaseHas(Phone::class, [
            'id' => 3123,
            'phone' => '0912345678',
            'is_blacklisted' => false,
        ]);
    }
}

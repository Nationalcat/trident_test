<?php

namespace Tests\Unit\Filters;

use App\Filters\AbstractFilter;
use App\Models\Table;
use Illuminate\Database\Eloquent\Builder;
use Tests\TestCase;

class AbstractFilterTest extends TestCase
{
    /**
     * @test
     * @group AbstractFilter
     * @author 56
     * @description 從 filter 中找到 Query String 並執行附加查詢條件
     */
    public function mapping_filter_is_work(): void
    {
        // arrange
        // act
        $result = (new class extends AbstractFilter {
            public function id($value): Builder
            {
                return $this->query->where('tables.id', $value);
            }
        })->filterByDecorators(Table::query(), ['id' => '123'])->toSql();
        // assert
        self::assertSame('select * from "tables" where "tables"."id" = ?', $result);
    }

    /**
     * @test
     * @group AbstractFilter
     * @author 56
     * @description 排除 PHP magic methods
     */
    public function can_not_mapping_filter_using_magic_methods(): void
    {
        // arrange
        // act
        $result = (new class extends AbstractFilter {
            public function __debugInfo()
            {
                throw new \Exception('Tom tries to hack here!');
            }
        })->filterByDecorators(Table::query(), ['__debugInfo' => 'gg'])->toSql();
        // assert
        self::assertSame('select * from "tables"', $result);
    }
}

<?php

namespace App\Supports\Facades;

use App\Supports\IdHandler;
use Illuminate\Support\Facades\Facade;

/**
 * @method static encode(int ...$ids): string
 * @method static decode(string $encoded): array|int
 */
class ID extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return IdHandler::class;
    }
}

<?php

namespace App\Supports;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Sqids\Sqids;

readonly class IdHandler
{
    private Sqids $squids;

    public function __construct()
    {
        $this->squids = new Sqids(
            alphabet: config('app.id.alphabet'),
            minLength: 10
        );
    }

    public function encode(int ...$ids): string
    {
        logger($ids);
        return $this->squids->encode($ids);
    }

    /**
     * @throws Exception
     */
    public function decode(string $encoded): array|int
    {
        $ids = $this->squids->decode($encoded);
        if ($encoded !== $this->squids->encode($ids)) {
            Log::error("Invalid ID: $encoded");
            abort(Response::HTTP_NOT_FOUND);
        }

        return count($ids) === 1
            ? $ids[0]
            : $ids;
    }
}

<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp(): void
    {
        parent::setUp();
        DB::connection()->getPdo()->sqliteCreateFunction('UNIX_TIMESTAMP', fn($date) => strtotime($date));
        DB::connection()->getPdo()->sqliteCreateFunction('HOUR', fn($date) => Carbon::parse($date)->hour);
    }
}

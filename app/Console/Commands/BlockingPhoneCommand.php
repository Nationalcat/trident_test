<?php

namespace App\Console\Commands;

use App\Models\Phone;
use App\Models\Queue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BlockingPhoneCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:blocking-phone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Phone::joinSub(
            query: Queue
                ::select(['phone_id'])
                ->where('booked_at', '<', now())
                ->whereNull('check_in_at')
                ->groupBy('phone_id')
                ->having(DB::raw('COUNT(*)'), '>=', 3),
            as: 'should_block',
            first: 'should_block.phone_id',
            operator: '=',
            second: 'phones.id'
        )->update(['is_blacklisted' => true]);
    }
}

<?php

namespace App\Http\Controllers\Staff;

use App\Filters\Staff\QueueFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\QueueController\CheckInRequest;
use App\Http\Requests\Staff\QueueController\CreateRequest;
use App\Models\Phone;
use App\Models\Queue;
use App\Models\Table;
use App\Supports\Facades\ID;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    /**
     * @param  Request  $request
     *
     * @return JsonResponse
     * @see app/docs/Staff/QueueController/Index.yaml
     */
    public function index(Request $request): JsonResponse
    {
        // 需可查看每日所有取號紀錄
        $queues = Queue
            ::select([
                'id',
                'number',
                'seat',
                'booked_at',
                'check_in_at',
                'created_at',
                DB::raw(<<<SQL
                  dense_rank() OVER (
                    PARTITION BY
                      DATE(queues.booked_at),
                      queues.seat
                    ORDER BY
                      queues.id ASC) AS "queue_number"
                  SQL
                ),
            ])
            // 需可查看每日所有取號紀錄
            ->toFilter(new QueueFilter(), $request->all())
            ->orderByDesc('booked_at')
            ->paginate(min($request->get('per_page', 10), 50));
        $now = now();
        return response()->json([
            'data' => $queues->map(fn($queue) => [
                // 取號時間
                'created_at' => $queue->created_at->toDateTimeString(),
                // 取號當下等待組數 (需依桌位大小區別)
                'seat' => $queue->seat,
                'queue_number' => $queue->queue_number,
                'check_in_at' => $queue->check_in_at?->toDateTimeString(), // 入座時間
                'check_out_at' => $queue->check_out_at?->toDateTimeString(), // 離座時間
                // 是否失約
                'no_show' => $queue->check_in_at === null && $queue->booked_at->lt($now),
                'booked_at' => $queue->booked_at->toDateTimeString(),
            ]),
            'total' => $queues->total(),
        ]);
    }

    /**
     * @param  CreateRequest  $request
     *
     * @return JsonResponse
     * @see app/docs/Staff/QueueController/Store.yaml
     */
    public function store(CreateRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $phone = Phone::where('phone', $payload['phone'])->first();
        $queue = DB::transaction(static function () use ($phone, $payload): Queue {
            $start = Carbon::parse($payload['date']);
            $number = Queue
                ::lockForUpdate()
                ->whereBetween('booked_at', [
                    (clone $start)->startOfDay(),
                    (clone $start)->endOfDay(),
                ])
                ->max('number');

            return $phone->queues()->create([
                'number' => $number + 1,
                'name' => $payload['name'],
                'seat' => $payload['seat'],
                'is_online' => false,
                'is_activated' => true,
                'booked_at' => $start,
            ]);
        });

        $code = ID::encode($queue->id, $queue->number, $queue->seat);
        // TODO: 發簡訊

        return response()->json(route('api.queues.show', ['code' => $code]));
    }

    /**
     * @param  Request  $request
     *
     * @return JsonResponse
     * @see app/docs/Staff/QueueController/Report.yaml
     */
    public function report(Request $request): JsonResponse
    {
        $start = Carbon::parse($request->get('date'));
        $end = (clone $start)->endOfDay();

        $report = DB::table('queues')
            ->select([
                // 需可顯示遠端/現場取號的統計資料
                DB::raw('SUM(queues.is_online = 1) AS from_online'),
                DB::raw('SUM(queues.is_online = 0) AS from_site'),
            ])
            ->when('date', fn($query) => $query->whereBetween('booked_at', [
                $start,
                $end,
            ]))
            ->first();

        $avgWaitReports = DB::table('queues')
            ->select([
                // 需統計取號的平均候位時間，並區分餐期與非餐期
                DB::raw('AVG(UNIX_TIMESTAMP(check_in_at) - UNIX_TIMESTAMP(booked_at)) AS avg_wait_time'),
                DB::raw('HOUR(booked_at) AS started_hour'),
            ])
            ->when('date', fn($query) => $query->whereBetween('booked_at', [
                $start,
                $end,
            ]))
            ->groupBy([
                'started_hour',
            ])
            ->orderBy('started_hour')
            ->get();

        return response()->json([
            'from_online' => (int) $report->from_online,
            'from_site' => (int) $report->from_site,
            'avg_wait_reports' => $avgWaitReports->map(fn($report) => [
                'started_hour' => (int) $report->started_hour,
                'avg_wait_time' => (float) $report->avg_wait_time,
            ]),
        ]);
    }

    /**
     * @param  CheckInRequest  $request
     *
     * @return JsonResponse
     * @see app/docs/Staff/QueueController/CheckIn.yaml
     */
    public function checkIn(CheckInRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $table = Table::find($payload['table_id']);
        $queue = Queue
            ::where('id', $payload['id'])
            ->where('booked_at', '<=', now())
            ->firstOrFail();

        if ($table->seat < $queue->seat) {
            return response()->json('座位數不足', 400);
        }

        $queue->update([
            'table_id' => $table->id,
            'check_in_at' => now(),
        ]);

        return response()->json('ok');
    }

    /**
     * @param  int  $id
     *
     * @return JsonResponse
     * @see app/docs/Staff/QueueController/CheckOut.yaml
     */
    public function checkOut(int $id): JsonResponse
    {
        Queue
            ::where('table_id', $id)
            ->where('booked_at', '<=', now())
            ->whereNull('check_out_at')
            ->update([
                'check_out_at' => now(),
            ]);

        return response()->json('ok');
    }
}

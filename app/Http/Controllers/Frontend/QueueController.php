<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\QueueController\CreateRequest;
use App\Models\Phone;
use App\Models\Queue;
use App\Models\Table;
use App\Supports\Facades\ID;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    /**
     * @return JsonResponse
     * @see app/docs/Frontend/QueueController/Index.yaml
     */
    public function index(): JsonResponse
    {
        // 取得有效桌號
        $tables = Table
            ::select([
                'seat',
            ])
            ->with([
                'queueBySeat' => fn(HasOne $query) => $query
                    /** @var Queue $query */
                    ->select([
                        'queues.seat',
                        DB::raw('MAX(queues.number) as latest_number'),
                        DB::raw('MIN(queues.number) as current_number'),
                        DB::raw('COUNT(queues.id) as queue_count'),
                    ])
                    ->inQueued()
                    ->groupBy([
                        'queues.seat',
                    ]),
            ])
            ->groupBy([
                'tables.seat',
            ])
            ->get();

        return response()->json($tables->map(fn($table) => [
            'seat' => $table->seat,
            'current_number' => $table->queueBySeat->current_number ?? null,
            'latest_number' => $table->queueBySeat->latest_number ?? null,
            'queue_count' => $table->queueBySeat->queue_count ?? 0,
        ]));
    }

    /**
     * @param  CreateRequest  $request
     *
     * @return JsonResponse
     * @see app/docs/Frontend/QueueController/Store.yaml
     */
    public function store(CreateRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $phone = Phone::where('phone', $payload['phone'])->first();
        if ($phone->is_blacklisted) {
            return response()->json('爽約多次，請現場候位', 400);
        }

        $queue = DB::transaction(static function () use ($phone, $payload): Queue {
            $now = now();
            $number = Queue
                ::lockForUpdate()
                ->whereBetween('booked_at', [
                    (clone $now)->startOfDay(),
                    (clone $now)->endOfDay(),
                ])
                ->max('number');

            return $phone->queues()->create([
                'number' => $number + 1,
                'name' => $payload['name'],
                'seat' => $payload['seat'],
                'is_online' => true,
                'is_activated' => true,
                'booked_at' => $now,
            ]);
        });

        $code = ID::encode($queue->id, $queue->number, $queue->seat);
        // TODO: 發簡訊

        return $this->show($code);
    }

    /**
     * @param  string  $code
     *
     * @return JsonResponse
     * @see app/docs/Frontend/QueueController/Show.yaml
     */
    public function show(string $code): JsonResponse
    {
        [$id, $number, $seat] = ID::decode($code);
        $target = Queue::findOrFail($id);
        $queue = Queue
            ::select([
                'queues.seat', // 桌子大小
                DB::raw('COUNT(queues.id) as queue_count'),
                DB::raw('MIN(queues.number) as min_number'),
            ])
            // 有效的預約
            ->inQueued()
            // 排在前面的號碼
            ->where('number', '<', $number)
            ->where('seat', $seat)
            ->whereBetween('booked_at', [
                (clone $target->booked_at)->startOfDay(),
                (clone $target->booked_at)->endOfDay(),
            ])
            ->groupBy([
                'queues.seat',
            ])
            ->first();

        return response()->json([
            'seat' => $seat,
            'your_number' => $number,
            'current_number' => $queue->min_number ?? null,
            'queue_count' => $queue->queue_count ?? 0,
        ]);
    }
}

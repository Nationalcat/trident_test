<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

trait QueueScope
{
    public function scopeInQueued(Builder $query, Carbon $date = null): void
    {
        $date ??= now();
        $query
            // 預約用餐時間內
            ->whereBetween('queues.booked_at', [(clone $date)->startOfDay(), $date])
            // 未入場
            ->whereNull('queues.check_in_at');
    }
}

<?php

namespace App\Filters\Staff;

use App\Filters\AbstractFilter;
use Illuminate\Database\Eloquent\Builder;

class QueueFilter extends AbstractFilter
{
    public function date(string $date): Builder
    {
        return $this->query->whereBetween('booked_at', [
            now()->parse($date)->startOfDay(),
            now()->parse($date)->endOfDay(),
        ]);
    }

    public function phone(string $phone): Builder
    {
        return $this->query->whereHas('phone', fn($query) => $query
            ->where('phone', $phone));
    }
}

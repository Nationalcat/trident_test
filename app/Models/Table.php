<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_activated',
        'seat',
        'size',
    ];

    protected $casts = [
        'is_activated' => 'boolean',
        'seat' => 'integer',
    ];

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class, 'queues.seat', 'tables.seat');
    }

    public function queueBySeat(): HasOne
    {
        return $this->hasOne(Queue::class, 'seat', 'seat');
    }
}

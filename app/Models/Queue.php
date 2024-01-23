<?php

namespace App\Models;

use App\Models\Scopes\QueueScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Queue extends Model
{
    use HasFactory;
    use QueueScope;

    protected $fillable = [
        'name',
        'number',
        'phone_id',
        'table_id',
        'is_activated',
        'is_online',
        'seat',
        'booked_at',
        'check_in_at',
        'check_out_at',
    ];

    protected $casts = [
        'name' => 'string',
        'number' => 'integer',
        'phone_id' => 'integer',
        'table_id' => 'integer',
        'is_activated' => 'boolean',
        'is_online' => 'boolean',
        'seat' => 'integer',
        'booked_at' => 'datetime:Y-m-d H:i:s',
        'check_in_at' => 'datetime:Y-m-d H:i:s',
        'check_out_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function phone(): BelongsTo
    {
        return $this->belongsTo(Phone::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }
}

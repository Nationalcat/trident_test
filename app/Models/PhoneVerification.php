<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhoneVerification extends Model
{
    protected $fillable = [
        'phone',
        'code',
        'expired_at',
    ];
    protected $casts = [
        'phone' => 'string',
        'code' => 'string',
        'expired_at' => 'datetime',
    ];

    public function phone(): BelongsTo
    {
        return $this->belongsTo(Phone::class);
    }
}

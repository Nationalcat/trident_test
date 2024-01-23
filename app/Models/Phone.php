<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Phone extends Model
{
    use HasFactory;
    protected $fillable = [
        'phone',
        'is_blacklisted',
    ];

    protected $casts = [
        'phone' => 'integer',
        'is_blacklisted' => 'boolean',
    ];

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    public function verification(): HasOne
    {
        return $this->hasOne(PhoneVerification::class)->latest('expired_at');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LotteryPrize extends Model
{
    use HasFactory;

    protected $fillable = [
        'lottery_id',
        'position',
        'amount',
        'rank'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function lottery(): BelongsTo
    {
        return $this->belongsTo(Lottery::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(LotteryResult::class);
    }
}

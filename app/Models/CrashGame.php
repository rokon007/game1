<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrashGame extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_hash',
        'crash_point',
        'status',
        'started_at',
        'crashed_at',
    ];

    protected $casts = [
        'crash_point' => 'decimal:2',
        'started_at' => 'datetime',
        'crashed_at' => 'datetime',
    ];

    public function bets(): HasMany
    {
        return $this->hasMany(CrashBet::class);
    }

    public function activeBets(): HasMany
    {
        return $this->hasMany(CrashBet::class)
            ->whereIn('status', ['pending', 'playing']);
    }

    public function getTotalBetAmountAttribute(): float
    {
        return (float) $this->bets()->sum('bet_amount');
    }

    public function getTotalPayoutAttribute(): float
    {
        return (float) $this->bets()
            ->where('status', 'won')
            ->sum('profit');
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isCrashed(): bool
    {
        return $this->status === 'crashed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}

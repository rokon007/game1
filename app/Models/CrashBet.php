<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrashBet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'crash_game_id',
        'bet_amount',
        'cashout_at',
        'profit',
        'status',
        'cashed_out_at',
    ];

    protected $casts = [
        'bet_amount' => 'decimal:2',
        'cashout_at' => 'decimal:2',
        'profit' => 'decimal:2',
        'cashed_out_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(CrashGame::class, 'crash_game_id');
    }

    public function isWon(): bool
    {
        return $this->status === 'won';
    }

    public function isLost(): bool
    {
        return $this->status === 'lost';
    }

    public function isPlaying(): bool
    {
        return $this->status === 'playing';
    }
}

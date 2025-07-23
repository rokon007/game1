<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LotteryResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'lottery_id',
        'lottery_prize_id',
        'lottery_ticket_id',
        'user_id',
        'winning_ticket_number',
        'prize_amount',
        'drawn_at'
    ];

    protected $casts = [
        'prize_amount' => 'decimal:2',
        'drawn_at' => 'datetime'
    ];

    public function lottery(): BelongsTo
    {
        return $this->belongsTo(Lottery::class);
    }

    public function prize(): BelongsTo
    {
        return $this->belongsTo(LotteryPrize::class, 'lottery_prize_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(LotteryTicket::class, 'lottery_ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

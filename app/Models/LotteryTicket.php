<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LotteryTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'lottery_id',
        'user_id',
        'ticket_number',
        'purchased_at'
    ];

    protected $casts = [
        'purchased_at' => 'datetime'
    ];

    public function lottery(): BelongsTo
    {
        return $this->belongsTo(Lottery::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function results(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LotteryResult::class, 'lottery_ticket_id');
    }

    public static function generateUniqueTicketNumber(): string
    {
        do {
            $ticketNumber = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        } while (self::where('ticket_number', $ticketNumber)->exists());

        return $ticketNumber;
    }
}

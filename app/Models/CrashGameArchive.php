<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrashGameArchive extends Model
{
    use HasFactory;

    protected $fillable = [
        'archive_date',
        'total_games',
        'total_bets',
        'total_bet_amount',
        'total_payout',
        'house_profit',
        'average_crash_point',
        'highest_crash_point',
        'lowest_crash_point',
        'additional_stats',
    ];

    protected $casts = [
        'total_bet_amount' => 'decimal:8',
        'total_payout' => 'decimal:8',
        'house_profit' => 'decimal:8',
        'average_crash_point' => 'decimal:2',
        'highest_crash_point' => 'decimal:2',
        'lowest_crash_point' => 'decimal:2',
        'additional_stats' => 'array',
    ];
}

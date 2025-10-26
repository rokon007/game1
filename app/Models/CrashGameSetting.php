<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrashGameSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_edge',
        'min_multiplier',
        'max_multiplier',
        'bet_waiting_time',
        'min_bet_amount',
        'max_bet_amount',
        'is_active',
        'multiplier_increment',
        'multiplier_interval_ms',
        'max_speed_multiplier',
        'enable_auto_acceleration',
        'speed_profile'
    ];

    protected $casts = [
        'house_edge' => 'decimal:4',
        'min_multiplier' => 'decimal:2',
        'max_multiplier' => 'decimal:2',
        'min_bet_amount' => 'decimal:2',
        'max_bet_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'multiplier_increment' => 'decimal:4',
        'multiplier_interval_ms' => 'integer',
        'max_speed_multiplier' => 'decimal:2',
        'enable_auto_acceleration' => 'boolean'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lottery extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'draw_date',
        'status',
        'auto_draw',
        'pre_selected_winners'
    ];

    protected $casts = [
        'draw_date' => 'datetime',
        'pre_selected_winners' => 'array',
        'price' => 'decimal:2'
    ];

    public function prizes(): HasMany
    {
        return $this->hasMany(LotteryPrize::class)->orderBy('rank');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(LotteryTicket::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(LotteryResult::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->draw_date > now();
    }

    public function isDrawTime(): bool
    {
        return $this->draw_date <= now() && $this->status === 'active';
    }

    public function getTotalTicketsSold(): int
    {
        return $this->tickets()->count();
    }

    public function getTotalRevenue(): float
    {
        return $this->getTotalTicketsSold() * $this->price;
    }
}

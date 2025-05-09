<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'scheduled_at',
     'ticket_price', 'is_active'
    ];

    protected $casts = [
    'scheduled_at' => 'datetime',
    'created_at' => 'datetime',
    'updated_at' => 'datetime'
    ];

    // A game can have many players
    public function players()
    {
        return $this->hasMany(Player::class);
    }

    // A game can have many announcements
    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    // A game can have many tickets
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}

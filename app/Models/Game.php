<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'max_players', 'status', 'start_time'];

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
        return $this->hasManyThrough(Ticket::class, Player::class);
    }
}

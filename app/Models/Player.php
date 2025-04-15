<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'game_id', 'score', 'winner'];

    // A player belongs to a game
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    // A player can have many tickets
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    // A player belongs to a user (assuming users exist in the system)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

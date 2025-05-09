<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'game_id',
        'ticket_number', 'is_winner','numbers'
    ];

    // A ticket belongs to a player
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
    public function getMorphClass()
    {
        return 'ticket';
    }
}

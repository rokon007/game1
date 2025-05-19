<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'game_id',
        'ticket_number',
        'numbers',
        'is_winner',
        'winning_patterns', // Add this to fillable
    ];

    protected $casts = [
        'numbers' => 'array',
        'is_winner' => 'boolean',
        'winning_patterns' => 'array', // Add this to casts
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function winnings()
    {
        return $this->hasMany(Winner::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = ['game_id', 'number'];

    // An announcement belongs to a game
    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}

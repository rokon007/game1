<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = ['player_id', 'numbers'];

    // A ticket belongs to a player
    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}

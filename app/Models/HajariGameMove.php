<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HajariGameMove extends Model
{
    use HasFactory;

    protected $table = 'hajari_game_moves';

    protected $fillable = [
        'hajari_game_id',
        'player_id',
        'round',
        'turn_order',
        'cards_played',
        'points_earned'
    ];

    protected $casts = [
        'cards_played' => 'array',
        'points_earned' => 'integer'
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(HajariGame::class, 'hajari_game_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_id');
    }
}

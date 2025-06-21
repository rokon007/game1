<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HajariGameParticipant extends Model
{
    use HasFactory;

    protected $table = 'hajari_game_participants';

    protected $fillable = [
        'hajari_game_id',
        'user_id',
        'status',
        'position',
        'cards',
        'score',
        'total_points',
        'rounds_won',
        'round_scores',
        'hazari_count',
        'is_ready'
    ];

    protected $casts = [
        'cards' => 'array',
        'round_scores' => 'array',
        'is_ready' => 'boolean',
        'total_points' => 'integer',
        'rounds_won' => 'integer',
        'hazari_count' => 'integer'
    ];

    // Define valid status values
    const STATUS_INVITED = 'invited';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DECLINED = 'declined';
    const STATUS_JOINED = 'joined';
    const STATUS_PLAYING = 'playing';
    const STATUS_FINISHED = 'finished';

    public function game(): BelongsTo
    {
        return $this->belongsTo(HajariGame::class, 'hajari_game_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sortCards(): void
    {
        if (!$this->cards) return;

        $sorted = collect($this->cards)->sortBy(function ($card) {
            $suitOrder = ['spades' => 4, 'hearts' => 3, 'diamonds' => 2, 'clubs' => 1];
            return ($suitOrder[$card['suit']] * 100) + $card['value'];
        })->values()->toArray();

        $this->update(['cards' => $sorted]);
    }

    // Helper methods for status checking
    public function isPlaying(): bool
    {
        return $this->status === self::STATUS_PLAYING;
    }

    public function isJoined(): bool
    {
        return $this->status === self::STATUS_JOINED;
    }

    public function canPlay(): bool
    {
        return in_array($this->status, [self::STATUS_JOINED, self::STATUS_PLAYING]);
    }
}

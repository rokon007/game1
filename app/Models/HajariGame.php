<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HajariGame extends Model
{
    use HasFactory;

    protected $table = 'hajari_games';

    protected $fillable = [
        'creator_id',
        'title',
        'description',
        'bid_amount',
        'max_players',
        'scheduled_at',
        'status',
        'game_settings',
        'winner_id'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'game_settings' => 'array',
        'bid_amount' => 'decimal:2'
    ];

    // Define valid status values
    const STATUS_PENDING = 'pending';
    const STATUS_WAITING = 'waiting';
    const STATUS_PLAYING = 'playing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(HajariGameParticipant::class, 'hajari_game_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(HajariGameInvitation::class, 'hajari_game_id');
    }

    public function moves(): HasMany
    {
        return $this->hasMany(HajariGameMove::class, 'hajari_game_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'hajari_game_id');
    }

    public function canJoin(User $user): bool
    {
        return $this->status === self::STATUS_PENDING &&
               $this->participants()->count() < $this->max_players &&
               !$this->participants()->where('user_id', $user->id)->exists();
    }

    public function isParticipant(User $user): bool
    {
        return $this->participants()->where('user_id', $user->id)->exists();
    }

    public function canStart(): bool
    {
        return $this->status === self::STATUS_PENDING &&
               $this->participants()->whereIn('status', [
                   HajariGameParticipant::STATUS_JOINED,
                   HajariGameParticipant::STATUS_ACCEPTED
               ])->count() === $this->max_players;
    }

    public function generateDeck(): array
    {
        $suits = ['hearts', 'diamonds', 'clubs', 'spades'];
        $ranks = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
        $deck = [];

        foreach ($suits as $suit) {
            foreach ($ranks as $rank) {
                $deck[] = [
                    'suit' => $suit,
                    'rank' => $rank,
                    'value' => $this->getCardValue($rank)
                ];
            }
        }

        return collect($deck)->shuffle()->toArray();
    }

    private function getCardValue(string $rank): int
    {
        return match($rank) {
            'A' => 14,
            'K' => 13,
            'Q' => 12,
            'J' => 11,
            default => (int) $rank
        };
    }
}

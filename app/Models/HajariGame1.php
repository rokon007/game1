<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HajariGame extends Model
{
    use HasFactory;

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

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function participants()
    {
        return $this->hasMany(HajariGameParticipant::class);
    }

    public function invitations()
    {
        return $this->hasMany(HajariGameInvitation::class);
    }

    public function moves()
    {
        return $this->hasMany(HajariGameMove::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function canJoin(User $user): bool
    {
        return $this->status === 'pending' &&
               $this->participants()->count() < $this->max_players &&
               !$this->participants()->where('user_id', $user->id)->exists();
    }

    public function isParticipant(User $user): bool
    {
        return $this->participants()->where('user_id', $user->id)->exists();
    }

    public function canStart(): bool
    {
        return $this->status === 'pending' &&
               $this->participants()->where('status', 'joined')->count() === $this->max_players;
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

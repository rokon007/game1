<?php

namespace App\Events;

use App\Models\HajariGame;
use App\Models\HajariGameParticipant;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoreUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public HajariGame $game,
        public HajariGameParticipant $participant,
        public int $pointsEarned,
        public int $round,
        public string $scoreType = 'normal' // normal, hazari, bonus
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('game.' . $this->game->id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'game_id' => $this->game->id,
            'player_id' => $this->participant->user_id,
            'player_name' => $this->participant->user->name,
            'points_earned' => $this->pointsEarned,
            'total_points' => $this->participant->total_points,
            'round' => $this->round,
            'score_type' => $this->scoreType,
            'all_scores' => $this->game->participants()->with('user')->get()->map(function($p) {
                return [
                    'user_id' => $p->user_id,
                    'name' => $p->user->name,
                    'total_points' => $p->total_points,
                    'rounds_won' => $p->rounds_won,
                    'hazari_count' => $p->hazari_count
                ];
            })
        ];
    }
}

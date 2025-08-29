<?php

namespace App\Events;

use App\Models\HajariGame;
use App\Models\HajariGameParticipant;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HajariGameOver
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public HajariGame $game,
        public HajariGameParticipant $winner,
        public array $finalScores
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
            'winner' => [
                'id' => $this->winner->user_id,
                'name' => $this->winner->user->name,
                'total_points' => $this->winner->total_points,
                'rounds_won' => $this->winner->rounds_won,
                'hazari_count' => $this->winner->hazari_count
            ],
            'final_scores' => $this->finalScores,
            'prize_amount' => $this->game->bid_amount * 4
        ];
    }
}

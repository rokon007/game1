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

class RoundWinner implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public HajariGame $game,
        public HajariGameParticipant $winner,
        public int $round
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
            'winner_position' => $this->winner->position,
            'winner_name' => $this->winner->user->name,
            'winner_id' => $this->winner->user_id,
            'round' => $this->round,
            'points_earned' => $this->winner->total_points
        ];
    }
}

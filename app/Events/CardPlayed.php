<?php

namespace App\Events;

use App\Models\HajariGame;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CardPlayed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public HajariGame $game,
        public User $player,
        public array $cards,
        public int $round,
        public int $turn
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
            'player_id' => $this->player->id,
            'player_name' => $this->player->name,
            'cards' => $this->cards,
            'round' => $this->round,
            'turn' => $this->turn,
            'timestamp' => now()->toISOString(),
        ];
    }
}

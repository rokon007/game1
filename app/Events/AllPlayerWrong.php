<?php

namespace App\Events;

use App\Models\HajariGame;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AllPlayerWrong implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $game;

    public function __construct(
        HajariGame $game
    ) {
        $this->game = $game;
    }

    public function broadcastOn()
    {
        return new Channel('game.' . $this->game->id);
    }

    public function broadcastAs(): string
    {
        return 'game.allWrong';
    }

    public function broadcastWith(): array
    {
        return ['game_id' => $this->game->id];
    }
}

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

    public function __construct(
        public HajariGame $game
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('game.' . $this->game->id),
        ];
    }
}

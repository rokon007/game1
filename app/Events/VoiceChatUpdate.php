<?php

namespace App\Events;

use App\Models\HajariGame;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoiceChatUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $game;
    public $data;

    public function __construct(HajariGame $game, array $data)
    {
        $this->game = $game;
        $this->data = $data;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('game.' . $this->game->id);
    }

    public function broadcastWith()
    {
        return $this->data;
    }
}

<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WinnerAnnouncedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gameId;

    public function __construct($gameId)
    {
        $this->gameId = $gameId;
    }


    public function broadcastOn()
    {
        return new Channel('game.' . $this->gameId);
    }


    public function broadcastAs()
    {
         return 'game.winner';
    }

    public function broadcastWith()
    {
        return [
            'gameId' => $this->gameId
        ];
    }
}

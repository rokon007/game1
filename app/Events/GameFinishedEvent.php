<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameFinishedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gameId;
    public $winners;

    public function __construct($gameId, $winners)
    {
        $this->gameId = $gameId;
        $this->winners = $winners;
    }

    public function broadcastOn()
    {
        return new Channel('game.'.$this->gameId);
    }

    public function broadcastAs()
    {
        return 'game.finished';
    }
}

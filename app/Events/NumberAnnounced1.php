<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NumberAnnounced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // use InteractsWithSockets, SerializesModels;

    public $gameId;
    public $number;

    public function __construct($gameId, $number)
    {
        $this->gameId = $gameId;
        $this->number = $number;
    }

    public function broadcastOn()
    {
        return new Channel('game.' . $this->gameId);
    }

    public function broadcastAs()
    {
        return 'number.announced';
    }
}

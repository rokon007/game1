<?php

namespace App\Events;

use App\Models\Lottery;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DrawStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $lottery;

    public function __construct(Lottery $lottery)
    {
        $this->lottery = $lottery;
    }

    public function broadcastOn()
    {
        return new Channel('lottery-channel');
    }

    public function broadcastWith()
    {
        return [
            'lottery_id' => $this->lottery->id,
            'lottery_name' => $this->lottery->name,
            'message' => 'ড্র শুরু হয়েছে!'
        ];
    }
}

<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WinnerSoundEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gameId;
    public $winnerUserId;
    public $pattern;

    /**
     * Create a new event instance.
     */
    public function __construct($gameId,$winnerUserId,$pattern)
    {
        $this->gameId = $gameId;
        $this->winnerUserId = $winnerUserId;
        $this->pattern = $pattern;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        // Use consistent channel naming with game room
        return new Channel('game.' . $this->gameId);
    }

    /**
     * Broadcast event name (optional).
     */
    public function broadcastAs(): string
    {
        return 'winner.broadcasted';
    }

    /**
     * Data to broadcast with the event
     */
    public function broadcastWith(): array
    {
        return [
            'gId' => $this->gameId,
            'winnerUserId' => $this->winnerUserId,
            'pattern' => $this->pattern
        ];
    }
}

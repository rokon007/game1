<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BroadcastNoticeEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gameId;
    public $title;
    public $body;

    /**
     * Create a new event instance.
     */
    public function __construct($gameId, $title, $body)
    {
        $this->gameId = $gameId;
        $this->title = $title;
        $this->body = $body;
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
        return 'notice.broadcasted';
    }

    /**
     * Data to broadcast with the event
     */
    public function broadcastWith(): array
    {
        return [
            'gameId' => $this->gameId,
            'title' => $this->title,
            'body' => $this->body
        ];
    }
}

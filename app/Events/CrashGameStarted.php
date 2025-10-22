<?php

namespace App\Events;

use App\Models\CrashGame;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CrashGameStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CrashGame $game;
    public float $currentMultiplier;

    /**
     * Create a new event instance.
     */
    public function __construct(CrashGame $game, float $currentMultiplier = 1.00)
    {
        $this->game = $game;
        $this->currentMultiplier = $currentMultiplier;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('crash-game'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'game.started';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'game_id' => $this->game->id,
            'status' => 'running',
            'multiplier' => $this->currentMultiplier,
            'crash_point' => $this->game->crash_point,
        ];
    }
}

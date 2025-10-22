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

class CrashGameCrashed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CrashGame $game;

    /**
     * Create a new event instance.
     */
    public function __construct(CrashGame $game)
    {
        $this->game = $game;
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
        return 'game.crashed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'game_id' => $this->game->id,
            'status' => 'crashed',
            'crash_point' => $this->game->crash_point,
            'total_bets' => $this->game->total_bet_amount,
            'total_payouts' => $this->game->total_payout,
            'house_profit' => $this->game->total_bet_amount - $this->game->total_payout,
        ];
    }
}

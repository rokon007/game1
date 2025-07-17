<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WinnerSoundEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gameId;
    public $winnerUserId;
    public $pattern;
    public $winnerName;

    public function __construct($gameId, $winnerUserId, $pattern, $winnerName = null)
    {
        $this->gameId = $gameId;
        $this->winnerUserId = $winnerUserId;
        $this->pattern = $pattern;
        $this->winnerName = $winnerName;

        Log::info('WinnerSoundEvent created', [
            'gameId' => $this->gameId,
            'winnerUserId' => $this->winnerUserId,
            'pattern' => $this->pattern,
            'winnerName' => $this->winnerName
        ]);
    }

    public function broadcastOn(): Channel
    {
        $channel = new Channel('game.' . $this->gameId);

        Log::info('WinnerSoundEvent broadcasting on channel', [
            'channel' => 'game.' . $this->gameId,
            'gameId' => $this->gameId
        ]);

        return $channel;
    }

    public function broadcastAs(): string
    {
        Log::info('WinnerSoundEvent broadcast as: winner.broadcasted');
        return 'winner.broadcasted';
    }

    public function broadcastWith(): array
    {
        $data = [
            'gameId' => $this->gameId,
            'winnerUserId' => $this->winnerUserId,
            'pattern' => $this->pattern,
            'winnerName' => $this->winnerName,
            'timestamp' => now()->toISOString()
        ];

        Log::info('WinnerSoundEvent broadcasting with data', $data);

        return $data;
    }

    /**
     * Determine if this event should broadcast.
     */
    public function broadcastWhen(): bool
    {
        $shouldBroadcast = !empty($this->gameId) && !empty($this->winnerUserId) && !empty($this->pattern);

        Log::info('WinnerSoundEvent broadcast condition', [
            'shouldBroadcast' => $shouldBroadcast,
            'gameId' => $this->gameId,
            'winnerUserId' => $this->winnerUserId,
            'pattern' => $this->pattern
        ]);

        return $shouldBroadcast;
    }
}

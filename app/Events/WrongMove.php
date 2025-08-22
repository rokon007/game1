<?php

namespace App\Events;

use App\Models\HajariGame;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WrongMove implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $game;
    public $user;
    public $currentEvaluation;
    public $previousEvaluation;

    public function __construct(HajariGame $game, User $user, $currentEvaluation, $previousEvaluation)
    {
        $this->game = $game;
        $this->user = $user;
        $this->currentEvaluation = $currentEvaluation;
        $this->previousEvaluation = $previousEvaluation;
    }

    public function broadcastOn()
    {
        return new Channel('game.' . $this->game->id);
    }

    public function broadcastAs()
    {
        return 'WrongMove';
    }
}

<?php

namespace App\Events;

use App\Models\HajariGame;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WrongMove implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $game;
    public $user;
    public $message;

    public function __construct(HajariGame $game, User $user)
    {
        $this->game = $game;
        $this->user = $user;
        $this->message = $user->name . ' একটি ভুল চাল দিয়েছেন!';
    }

    public function broadcastOn()
    {
        return new PresenceChannel('game.' . $this->game->id);
    }

}

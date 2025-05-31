<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;


class NotificationRefresh
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $textNote;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, $textNote)
    {
        $this->user = $user;
        $this->textNote = $textNote;
    }

    public function broadcastWith()
    {
        return [
            'user_id' => $this->user->id,
            'text_note' => $this->textNote,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('notRefresh.' .$this->user->id),
        ];
    }
}

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
use App\Models\Conversation;
use App\Models\Message;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $conversation;
    public $receiver;
    public $message;
    /**
     * Create a new event instance.
     */
    public function __construct(User $user, Conversation $conversation, User $receiver, Message $message)
    {
        $this->user = $user;
        $this->conversation = $conversation;
        $this->receiver = $receiver;
        $this->message = $message;
    }

    public function broadcastWith()
    {
        return [
            'user_id' => $this->user->id,
            'conversation_id' => $this->conversation->id,
            'receiver_id' => $this->receiver->id,
            'message_id' => $this->message->id,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // error_log($this->user);
        // error_log($this->receiver);
        return
        [
         new PrivateChannel('chat.' .$this->receiver->id),
        ];
    }
}

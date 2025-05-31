<?php

namespace App\Livewire\Frontend\NewChat;

use Livewire\Component;
use Illuminate\Support\Str;
use App\Events\MessageSent;
use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;

class Sendmessage extends Component
{
    public $selectedConversation;
    public $receiverInstance;
    public $senderInstance;
    public $messageCreated;
    public string $body;

    protected $listeners = ['sendMessageEvent'];

    public function sendMessageEvent(Conversation $conversation, User $receiverId, User $senderId)
    {
        $this->selectedConversation = $conversation;
        $this->receiverInstance = $receiverId;
        $this->senderInstance = $senderId;
    }

    public function sendMessage()
    {
        if($this->body == null)
        {
            return null;
        }
       $this->messageCreated = Message::create([
            'conversation_id' => $this->selectedConversation->id,
            'sender_id' => auth()->user()->id,
            'receiver_id' => $this->receiverInstance->id,
            'body' => $this->body,
        ]);

        $this->selectedConversation->last_time_message = $this->messageCreated->created_at;
        $countWords = strlen($this->body);
        if($countWords <= 52)
        {
        $this->selectedConversation->update(['last_message' => $this->body]);
        }
        elseif($countWords > 52)
        {
        $cuttedMessage = Str::limit($this->body, 60);
        $this->selectedConversation->update(['last_message' => $cuttedMessage]);
        }
        $this->selectedConversation->save();
        $this->dispatch('refresh-me', $this->selectedConversation, $this->receiverInstance, $this->senderInstance);
        $this->reset('body');
        $this->dispatch('refresh-chatlist');
        $this->dispatch('new-message');

        if($this->selectedConversation->receiver_id != auth()->user()->id)
        {
        broadcast(new MessageSent(auth()->user(), $this->selectedConversation, $this->receiverInstance, $this->messageCreated));
        }
        elseif($this->selectedConversation->receiver_id == auth()->user()->id)
        {
            $this->receiverInstance = $this->senderInstance;
        broadcast(new MessageSent(auth()->user(), $this->selectedConversation, $this->receiverInstance, $this->messageCreated));

        }
    }

    public function render()
    {
        return view('livewire.frontend.new-chat.sendmessage', ['conversation' => $this->selectedConversation]);
    }

}

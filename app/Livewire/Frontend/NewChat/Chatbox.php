<?php

namespace App\Livewire\Frontend\NewChat;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;

class Chatbox extends Component
{
    public $selectedConversation;
    public $receiverInstance;
    public $senderInstance;
    public $messageCount;
    public $messages;
    public $paginate = 10;
    public $perPage = 8;
    public $page = 1;


    public function getListeners()
    {
        $auth_id = auth()->user()->id;
            return [
            "echo-private:chat.{$auth_id},MessageSent"=>"broadcastedMessageReceived",
            "echo-private:chat.{$auth_id},MessageRead" => "broadcastedMessageRead",
            "refresh-unread-count" => '$refresh',
        ];
    }

    public function broadcastedMessageRead($event)
    {
        // Unread count refresh হবে
        $this->dispatch('refresh-unread-count');
    }


    public function broadcastedMessageReceived($event)
    {
        //dd($event);
      if($this->selectedConversation)
        {
            $broadcastedMessage = Message::find($event['message_id']);
            if($this->selectedConversation->id == $event['conversation_id'])
            {
                $this->messages->push($broadcastedMessage);
                $broadcastedMessage->read = 1;
                $broadcastedMessage->save();
                $this->dispatch('new-message');
                //$this->dispatch('scrol-bottom');
            }
        }
    }

    #[On('loadConversation')]
    public function loadConversation(Conversation $conversation, User $receiverId, User $senderId)
    {
        $total = $this->perPage * $this->page;
        $this->selectedConversation = $conversation;
        $this->receiverInstance = $receiverId;
        $this->senderInstance = $senderId;
        $this->messageCount = Message::where('conversation_id',$this->selectedConversation->id)->count();
        $this->messages = Message::where('conversation_id', $this->selectedConversation->id)
        ->skip($this->messageCount - $total)
        ->take($total)->get();
        // Mark all messages as read when conversation is loaded
        $this->markAllAsRead();
        // $this->dispatch('scrol-bottom');
    }

    #[On('refresh-me')]
    public function refresh()
    {
        $this->loadConversation($this->selectedConversation, $this->receiverInstance, $this->senderInstance);
        $this->dispatch('new-message');
    }

     #[On('loadMore1')]
    public function  loadMore()
    {
        //dd('loadMore1');
        $this->page++;
        $this->loadConversation($this->selectedConversation, $this->receiverInstance, $this->senderInstance);
    }

    /**
     * Mark all unread messages in current conversation as read
     */
    public function markAllAsRead()
    {
        if ($this->selectedConversation) {
            Message::where('conversation_id', $this->selectedConversation->id)
                ->where('receiver_id', auth()->id())
                ->where('read', 0)
                ->update(['read' => 1]);

            // Dispatch event if needed
            $this->dispatch('messages-marked-read');
        }
    }


    public function render()
    {
        return view('livewire.frontend.new-chat.chatbox',[
        'receiverInstance' => $this->receiverInstance,
        'senderInstance' => $this->senderInstance,
        'messages' => $this->messages,
    ]);
    }
}

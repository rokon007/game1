<?php

namespace App\Livewire\Frontend\NewChat;

use Livewire\Component;
use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use Livewire\Attributes\On;

class Main extends Component
{
    public $searchUser = "";
    public $results = [];
    public $searchMode=true;
    public $chatList=true;
    public $chatBox=false;

    #[On('searchModeHide')]
    public function searchModeHide()
    {
        $this->searchMode=false;
    }

    public function searchModeShow()
    {
        $this->searchMode=true;
    }

    #[On('chatListModeHide')]
    public function chatListModeHide()
    {
        $this->chatList=false;
    }


    #[On('chatBoxShow')]
    public function chatBoxShow()
    {
        $this->chatBox=true;
    }

    public function createConversation($receiverId)
    {
        // checking the conversation First if it exists or no
        $checkConversation = Conversation::where(function ($query) use ($receiverId) {
            $query->where('receiver_id', auth()->user()->id)
                  ->where('sender_id', $receiverId);
        })->orWhere(function ($query) use ($receiverId) {
            $query->where('receiver_id', $receiverId)
                  ->where('sender_id', auth()->user()->id);
        })->get();

        if(count($checkConversation) == 0)
        {
            // dd('no convo');
            $createdConversation = Conversation::create(['receiver_id' => $receiverId , 'sender_id' => auth()->user()->id, 'last_message' => 'Click to start chat']);

            $createMessage = Message::create(['conversation_id'=> $createdConversation->id, 'sender_id' => auth()->user()->id, 'receiver_id'=> $receiverId]);

            $createdConversation->last_time_message = $createMessage->created_at;
            $createdConversation->save();
            $this->dispatch('refresh-chatlist');

            // After creating a new conversation, immediately select it
            $receiverInstance = User::find($receiverId);
            $senderInstance = auth()->user();
            $this->dispatch('chatUserSelected1', conversation: $createdConversation, receiverId: $receiverInstance->id, senderId: $senderInstance->id);
            $this->reset();


        }
        elseif(count($checkConversation) >= 1)
        {
            // Find the specific conversation between the two users
            $conversation = $checkConversation->first(function ($convo) use ($receiverId) {
                return ($convo->sender_id == auth()->user()->id && $convo->receiver_id == $receiverId) ||
                       ($convo->sender_id == $receiverId && $convo->receiver_id == auth()->user()->id);
            });

            if ($conversation)
            {
               $receiverInstance = User::find($receiverId);
               $senderInstance = auth()->user(); // The current authenticated user is the sender in this context
               $this->dispatch('chatUserSelected1', conversation: $conversation, receiverId: $receiverInstance->id, senderId: $senderInstance->id);
            }
        }
    }

    public function render()
    {
        if(strlen($this->searchUser) >= 1)
        {
            $this->dispatch('chatListHide');
            $this->results = User::where('unique_id', 'like','%'. $this->searchUser. '%')->get();
        }else{
            $this->dispatch('chatListShow');
        }
        return view('livewire.frontend.new-chat.main', ['result' => $this->results])->layout('livewire.layout.frontend.base');
    }
}


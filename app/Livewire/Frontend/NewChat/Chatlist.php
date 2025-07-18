<?php

namespace App\Livewire\Frontend\NewChat;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;

class Chatlist extends Component
{
    public $conversations;
    public $selectedConversation;
    public $receiverInstance;
    public $senderInstance;
    public $chatList=true;
    public $users;

    // protected $listener = ['refresh-bra' => 'render'];

    public function getListeners()
    {
        $auth_id = auth()->user()->id;
            return [
            "echo-private:chat.{$auth_id},MessageSent"=>"broadcastedMessageReceived",
        ];
    }

    public function broadcastedMessageReceived($event)
    {
      $this->dispatch('refreshChatListForReceiver')->self();
    }

    #[On('chatUserSelected1')]
    public function chatUserSelected(Conversation $conversation, $receiverId, $senderId)
    {
        //dd($receiverId);
    //problem is here that changing the chat alignement or maybe with DESC
      $this->dispatch('searchModeHide');
      $this->dispatch('chatListModeHide');
      $this->dispatch('chatBoxShow');
      $this->selectedConversation = $conversation;
      $this->receiverInstance = User::find($receiverId);
      $this->senderInstance = User::find($senderId);

      $this->dispatch('loadConversation', $this->selectedConversation, $this->receiverInstance, $this->senderInstance)->to(Chatbox::class);
      $this->dispatch('sendMessageEvent', $this->selectedConversation, $this->receiverInstance, $this->senderInstance)->to(Sendmessage::class);
    }


    public function mount()
    {
      $this->conversations = Conversation::with(['senderInverseRelation','receiverInverseRelation'])->where('sender_id', auth()->user()->id)->orWhere('receiver_id', auth()->user()->id)->orderBy('last_time_message', 'DESC')->get();
      // dd($this->conversations);
      $this->users = User::where('id', '!=', Auth::id())
            ->where('status', 'active')
            ->where('role','agent')
            ->get();
    }

    public function createConversation($receiverId)
    {
        //checking the conversation First if it exists or no
        $checkConversation = Conversation::where(function ($query) use ($receiverId) {
            $query->where('receiver_id', auth()->user()->id)
                  ->where('sender_id', $receiverId);
        })->orWhere(function ($query) use ($receiverId) {
            $query->where('receiver_id', $receiverId)
                  ->where('sender_id', auth()->user()->id);
        })->get();

        if(count($checkConversation)==0)
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
            $this->chatUserSelected( $createdConversation, $receiverInstance->id, $senderInstance->id);
            //$this->reset();


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
                $this->chatUserSelected( $conversation, $receiverInstance->id, $senderInstance->id);
            }
        }
    }

    #[On('chatListShow')]
    public function chatListShow()
    {
        $this->chatList=true;
    }

    #[On('chatListHide')]
    public function chatListHide()
    {
        $this->chatList=false;
    }

    #[On('refreshChatListForReceiver')]
    #[On('refresh-chatlist')]
    public function render()
    {
        $this->conversations = Conversation::with(['senderInverseRelation','receiverInverseRelation'])->where('sender_id', auth()->user()->id)->orWhere('receiver_id', auth()->user()->id)->orderBy('last_time_message', 'DESC')->get();
        return view('livewire.frontend.new-chat.chatlist',[
          'conversations' => $this->conversations, // Fix: Pass conversations with key
        ]);
    }
}


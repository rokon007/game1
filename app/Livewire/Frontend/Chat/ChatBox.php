<?php

namespace App\Livewire\Frontend\Chat;

use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;

class ChatBox extends Component
{
    use WithFileUploads;

    public $conversationId = null;
    public $conversation = null;
    public $message = '';
    public $messages = [];
    public $loadedMessages = false;
    public $attachment = null;
    public $typingUsers = [];
    public $onlineUsers = [];

    public $perPage = 12;
    public $page = 1;

    // protected $listeners=[
    //      'loadMore'
    // ];

    public function mount()
    {
         //$this->conversationId = 4;
        $this->typingUsers = []; // Initialize with empty array
    }

    // Dynamic listeners setup
    public function getListeners()
    {
        //dd($this->conversationId);
        return [
            'conversationSelected' => 'selectConversation',
            'onlineUsersUpdated' => 'handleOnlineUsersUpdated',
            'typingStatusUpdated' => 'handleTypingStatus',
            'loadMore',
            "echo-presence:conversation.{$this->conversationId},MessageSent" => 'notifyNewMessage',
            //"echo-presence:conversation.{$this->conversationId},UserTyping" => 'handleUserTyping',
        ];
    }


    //  public function getListeners()
    // {
    //     $auth_id = auth()->user()->id;
    //         return [
    //         "echo-presence:conversation.{$auth_id},MessageSent"=>"notifyNewMessage1",
    //         'conversationSelected' => 'selectConversation',
    //         'onlineUsersUpdated' => 'handleOnlineUsersUpdated',
    //         'typingStatusUpdated' => 'handleTypingStatus',
    //         'loadMore',
    //     ];
    // }



    public function notifyNewMessage1()
    {
       \Illuminate\Support\Facades\Log::info('notifyNewMessage1 method called successfully!');
    }

    public function handleOnlineUsersUpdated($onlineUsers)
    {
        $this->onlineUsers = $onlineUsers;
        $this->dispatch('onlineStatusUpdated', $onlineUsers);
    }

    public function selectConversation($conversationId)
    {
        $this->conversationId = $conversationId;
        $this->conversation = Conversation::with(['users' => function($query) {
            $query->where('users.id', '!=', Auth::id());
        }])->find($conversationId);

        $this->loadMessages();
        $this->markAsRead();
        $this->message = '';
        $this->attachment = null;
    }

    public function loadMessages()
    {
        if ($this->conversationId) {
            $total = $this->perPage * $this->page;

            $this->messages = Message::where('conversation_id', $this->conversationId)
                ->with('user')
                ->orderBy('created_at', 'desc') // Descending order
                ->take($total)
                ->get()
                ->reverse(); // So latest messages come at bottom

            $this->loadedMessages = true;

            if ($this->page === 1) {
                $this->markAsRead();
                $this->dispatch('sound');
            }
        }
    }
    #[On('loadMore1')]
    public function  loadMore()
    {
        $this->page++;
        $this->loadMessages();
    }



    public function markAsRead()
    {
        if ($this->conversationId) {
            $pivotRow = Auth::user()->conversations()->where('conversation_id', $this->conversationId)->first()->pivot;
            $pivotRow->markAsRead();

            $this->dispatch('refresh');
        }
    }

    public function sendMessage()
    {
        if ((empty($this->message) && !$this->attachment) || !$this->conversationId) {
            return;
        }

        $messageData = [
            'conversation_id' => $this->conversationId,
            'user_id' => Auth::id(),
            'body' => $this->message ?? '',
        ];

        if ($this->attachment) {
            $path = $this->attachment->store('attachments', 'public');
            $messageData['attachment'] = $path;

            $extension = $this->attachment->getClientOriginalExtension();
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array(strtolower($extension), $imageExtensions)) {
                $messageData['attachment_type'] = 'image';
            } else {
                $messageData['attachment_type'] = 'document';
            }
        }

        $message = Message::create($messageData);

        $this->message = '';
        $this->attachment = null;

        $message->load('user');
        broadcast(new MessageSent($message))->toOthers();

        // Reset typing status when sending a message
        //broadcast(new UserTyping(Auth::user(), $this->conversationId, false))->toOthers();

        $this->messages->push($message);
        $this->markAsRead();
        $this->dispatch('scrol-bottom');
    }

   // #[On('message-received')]
    public function notifyNewMessage($event)
    {
        dd('message-received');
        $message = Message::with('user')->find($event['message']['id']);
        if ($message) {
            $this->messages->push($message);
            $this->dispatch('messageReceived', $message);
        }
        $this->markAsRead();
    }

    public function updated($property)
    {
        if ($property === 'message' && $this->conversationId) {
            $isTyping = !empty($this->message);

            $this->dispatch('typingStatusUpdated', [
                'user_id' => Auth::id(),
                'is_typing' => $isTyping,
                'name' => Auth::user()->name
            ]);

            // Broadcast typing status to others
            broadcast(new UserTyping(Auth::user(), $this->conversationId, $isTyping))->toOthers();
        }
    }

    #[On('typingStatusUpdated')]
    public function handleTypingStatus($event)
    {
        $userId = $event['user_id'];
        $isTyping = $event['is_typing'];
        $userName = $event['name'] ?? 'User';

        if ($isTyping) {
            $this->typingUsers[$userId] = $userName;
        } else {
            if (isset($this->typingUsers[$userId])) {
                unset($this->typingUsers[$userId]);
            }
        }

        $this->dispatch('userTyping', [
            'userId' => $userId,
            'isTyping' => $isTyping,
            'userName' => $userName
        ]);
    }

    #[On('user-typing')]
    public function handleUserTyping($event)
    {
        $this->handleTypingStatus($event);
    }

    public function deleteMessage($messageId)
    {
        $message = Message::find($messageId);

        if ($message && $message->user_id === Auth::id()) {
            $message->delete();
            $this->loadMessages();
        }
    }

    public function render()
    {
        return view('livewire.frontend.chat.chat-box');
    }
}

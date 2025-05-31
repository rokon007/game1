<?php

namespace App\Livewire\Frontend\Chat;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class ChatList extends Component
{
    public $conversations = [];
    public $users = [];
    public $selectedConversation = null;
    public $query = '';
    public $showArchived = false;
    public $onlineUsers = [];
    public $listMode = true;
    public $allUsersMode = false;

    public function mount()
    {
        $this->onlineUsers = []; // Initialize with empty array
        $this->conversations = []; // Initialize with empty array
        $this->users = []; // Initialize with empty array

        $this->loadConversations();
        $this->loadUsers();
    }

    public function allUsers()
    {
        $this->listMode = false;
        $this->showArchived = false;
        $this->allUsersMode = true;
    }

    #[On('onlineUsersUpdated')]
    public function handleOnlineUsersUpdated($onlineUsers)
    {
        $this->onlineUsers = $onlineUsers;
    }

    #[On('conversationSelected')]
    public function conversationSelected($conversationId)
    {
        $this->selectedConversation = $conversationId;
    }

    #[On('refresh')]
    public function refresh()
    {
        $this->loadConversations();
    }

    public function loadConversations()
    {
        $query = Auth::user()->conversations()
            ->with(['lastMessage', 'users' => function($query) {
                $query->where('users.id', '!=', Auth::id());
            }]);

        if (!$this->showArchived) {
            $query->wherePivot('is_archived', false);
        }

        $this->conversations = $query->get()
            ->map(function($conversation) {
                $conversation->unread_count = $conversation->unreadMessagesForUser(Auth::user());
                return $conversation;
            });
    }

    public function loadUsers()
    {
        $this->users = User::where('id', '!=', Auth::id())
            ->where('status', 'active')
            ->when($this->query, function($query) {
                return $query->where('name', 'like', '%' . $this->query . '%')
                    ->orWhere('email', 'like', '%' . $this->query . '%')
                    ->orWhere('mobile', 'like', '%' . $this->query . '%');
            })
            ->get();
    }

    public function startConversation($userId)
    {
        $user = User::find($userId);

        // Check if conversation already exists
        $conversation = Auth::user()->getConversationWith($userId);

        if (!$conversation) {
            // Create new conversation
            $conversation = Conversation::create([
                'is_group' => false
            ]);

            $conversation->users()->attach([Auth::id(), $userId]);
        }

        $this->dispatch('conversationSelected', $conversation->id);
        $this->allUsersMode = false;
        $this->listMode = false;
    }

    public function selectConversation($conversationId)
    {
        $this->selectedConversation = $conversationId;
        $this->dispatch('conversationSelected', $conversationId);
        $this->listMode = false;
    }

    public function toggleArchived()
    {
        $this->showArchived = !$this->showArchived;
        $this->loadConversations();
    }

    public function toggleMute($conversationId)
    {
        $pivotRow = Auth::user()->conversations()->where('conversation_id', $conversationId)->first()->pivot;
        $pivotRow->toggleMute();
        $this->loadConversations();
    }

    public function toggleArchive($conversationId)
    {
        $pivotRow = Auth::user()->conversations()->where('conversation_id', $conversationId)->first()->pivot;
        $pivotRow->toggleArchive();
        $this->loadConversations();
    }

    public function render()
    {
        return view('livewire.frontend.chat.chat-list');
    }
}

<?php

namespace App\Livewire\Frontend\Header;

use Livewire\Component;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;

class UnreadChatCount extends Component
{
    public $unreadCount = 0;
    public $previousUnreadCount = 0;

     public function getListeners()
    {
        $userId = Auth::id();

        return [
            'refresh-unread-count' => 'updateUnreadCount',
            "echo-private:chat.{$userId},MessageSent" => 'handleNewMessage',
            "echo-private:chat.{$userId},MessageRead" => 'updateUnreadCount',
        ];
    }

    public function mount()
    {
        $this->previousUnreadCount = $this->getCurrentUnreadCount();
        $this->unreadCount = $this->previousUnreadCount;
    }

    public function handleNewMessage($event)
    {
        $this->updateUnreadCount();

        // শুধুমাত্র নতুন মেসেজ আসলে সাউন্ড প্লে করবে
        if ($this->unreadCount > $this->previousUnreadCount) {
            $this->dispatch('play-message-sound');
        }

        $this->previousUnreadCount = $this->unreadCount;
    }

    public function updateUnreadCount()
    {
        $this->unreadCount = $this->getCurrentUnreadCount();
    }

    protected function getCurrentUnreadCount()
    {
        if (!auth()->check()) {
            return 0;
        }

        return Conversation::where(function($query) {
                $query->where('sender_id', auth()->id())
                      ->orWhere('receiver_id', auth()->id());
            })
            ->withCount(['messages' => function($query) {
                $query->where('read', 0)
                      ->where('receiver_id', auth()->id());
            }])
            ->get()
            ->sum('messages_count');
    }

    public function render()
    {
        return view('livewire.frontend.header.unread-chat-count');
    }
}

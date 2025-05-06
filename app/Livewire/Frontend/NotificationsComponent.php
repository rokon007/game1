<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NotificationsComponent extends Component
{
    public $notifications = [];
    public $unreadCount = 0;
    public $detailsMode = false;
    public $selectedNotification;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $user = Auth::user();
        $this->notifications = $user->notifications; // সব নোটিফিকেশন
        $this->unreadCount = $user->unreadNotifications->count(); // আনরেড নোটিফিকেশন কাউন্ট
        $this->dispatch('updateCartCount');
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->find($id);
        if ($notification && $notification->read_at === null) {
            $notification->markAsRead(); // আনরেড থেকে রিড করা
            $this->loadNotifications();
        }
    }

    public function details($id)
    {
        $this->selectedNotification = Auth::user()->notifications()->find($id);
        $this->detailsMode = true;
        $this->markAsRead($id);
    }

    public function backToList()
    {
        $this->detailsMode = false;
        $this->selectedNotification = null;
    }

    public function render()
    {
        return view('livewire.frontend.notifications-component')->layout('livewire.layout.frontend.base');
    }
}

<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class NotificationsComponent extends Component
{
    public $notifications = [];
    public $unreadCount = 0;
    public $detailsMode = false;
    public $selectedNotification;

    public $perPage = 10;
    public $loadedCount = 0;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        // ব্যবহারকারী প্রমাণীকৃত কিনা চেক করা
        if (!Auth::check()) {
            $this->notifications = [];
            $this->unreadCount = 0;
            return;
        }

        $user = Auth::user();

        // প্রাথমিক নোটিফিকেশন লোড: নতুন থেকে পুরানো
        $this->notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->take($this->perPage)
            ->get();

        $this->loadedCount = $this->notifications->count();
        $this->unreadCount = $user->unreadNotifications()->count();

        // আনরিড কাউন্ট আপডেটের জন্য ইভেন্ট
        $this->dispatch('updateNotificationCount', $this->unreadCount);
    }

    #[On('loadMore')]
    public function loadMore()
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        // নতুন নোটিফিকেশন লোড
        $newNotifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->skip($this->loadedCount)
            ->take($this->perPage)
            ->get();

        // তালিকার শেষে নতুন নোটিফিকেশন যোগ
        $this->notifications = $this->notifications->merge($newNotifications);
        $this->loadedCount += $newNotifications->count();
        $this->unreadCount = $user->unreadNotifications()->count();

        $this->dispatch('updateNotificationCount', $this->unreadCount);
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->find($id);
        if ($notification && $notification->read_at === null) {
            $notification->markAsRead(); // আনরিড থেকে রিড করা
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
?>

<?php

namespace App\Livewire\Frontend\Chat;

use App\Events\UserOnlineStatus;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OnlineStatus extends Component
{
    public $onlineUsers = [];

    protected $listeners = [
        'echo:online-status,UserOnlineStatus' => 'updateOnlineStatus',
    ];

    public function mount()
    {
        // Set current user as online
        if (Auth::check()) {
            $user = Auth::user();
            $user->is_online = true;
            $user->save();

            broadcast(new UserOnlineStatus($user, true))->toOthers();
        }

        // Get all online users
        $this->loadOnlineUsers();
    }

    public function loadOnlineUsers()
    {
        $this->onlineUsers = User::where('is_online', true)
            ->where('id', '!=', Auth::id())
            ->pluck('id')
            ->toArray();

        // Update other components
        $this->dispatch('onlineUsersUpdated', onlineUsers: $this->onlineUsers);
    }

    public function updateOnlineStatus($event)
    {
        $userId = $event['user_id'];
        $isOnline = $event['is_online'];

        if ($isOnline && !in_array($userId, $this->onlineUsers)) {
            $this->onlineUsers[] = $userId;
        } elseif (!$isOnline && in_array($userId, $this->onlineUsers)) {
            $this->onlineUsers = array_diff($this->onlineUsers, [$userId]);
        }

        // Update other components
        $this->dispatch('onlineUsersUpdated', onlineUsers: $this->onlineUsers);
    }

    // public function dehydrate()
    // {
    //     // When navigating away from page or closing page
    //     if (Auth::check()) {
    //         $user = Auth::user();
    //         $user->is_online = false;
    //         $user->save();

    //         broadcast(new UserOnlineStatus($user, false))->toOthers();
    //     }
    // }

    public function render()
    {
        return view('livewire.frontend.chat.online-status');
    }
}

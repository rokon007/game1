<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;

class UserOnlineStatus extends Component
{
    public $user;
    public $isOnline;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->isOnline = $user->is_online;
    }

    public function getListeners()
    {
        return [
            "echo-presence:online-users,here" => 'updateOnlineStatus',
            "echo-presence:online-users,joining" => 'userJoined',
            "echo-presence:online-users,leaving" => 'userLeft',
        ];
    }

    public function updateOnlineStatus($users)
    {
        $this->isOnline = collect($users)->contains('id', $this->user->id);
    }

    public function userJoined($user)
    {
        if ($user['id'] == $this->user->id) {
            $this->isOnline = true;
        }
    }

    public function userLeft($user)
    {
        if ($user['id'] == $this->user->id) {
            $this->isOnline = false;
        }
    }

    public function render()
    {
        return view('livewire.user-online-status');
    }
}


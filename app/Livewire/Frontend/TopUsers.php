<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use App\Models\User;

class TopUsers extends Component
{
    public $topUsers;

    public function mount()
    {
        $this->topUsers = User::whereNotIn('role', ['admin', 'agent'])
                    ->orderByDesc('credit')
                    ->take(20)
                    ->get();
    }

    public function render()
    {
        return view('livewire.frontend.top-users')->layout('livewire.layout.frontend.base');
    }
}

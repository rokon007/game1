<?php

namespace App\Livewire\Frontend;

use Livewire\Component;

class GameLobby extends Component
{
    public function render()
    {
        return view('livewire.frontend.game-lobby')->layout('livewire.layout.frontend.base');
    }
}

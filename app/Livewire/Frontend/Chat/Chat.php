<?php

namespace App\Livewire\Frontend\Chat;

use Livewire\Component;

class Chat extends Component
{
    public function render()
    {
        return view('livewire.frontend.chat.chat')->layout('livewire.layout.frontend.base');
    }
}

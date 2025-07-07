<?php

namespace App\Livewire\Frontend;

use Livewire\Component;

class BannedUser extends Component
{
    public function render()
    {
        return view('livewire.frontend.banned-user')->layout('livewire.layout.frontend.base');
    }
}

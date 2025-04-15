<?php

namespace App\Livewire\Frontend;

use Livewire\Component;

class ProfileComponent extends Component
{
    public function render()
    {
        return view('livewire.frontend.profile-component')->layout('livewire.layout.frontend.base');
    }
}

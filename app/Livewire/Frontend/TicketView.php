<?php

namespace App\Livewire\Frontend;

use Livewire\Component;

class TicketView extends Component
{
    public function render()
    {
        return view('livewire.frontend.ticket-view')->layout('livewire.layout.frontend.base');
    }
}

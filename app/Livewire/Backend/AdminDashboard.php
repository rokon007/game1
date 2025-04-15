<?php

namespace App\Livewire\Backend;

use Livewire\Component;

class AdminDashboard extends Component
{
    public function render()
    {
        return view('livewire.backend.admin-dashboard')->layout('livewire.backend.base');
    }
}

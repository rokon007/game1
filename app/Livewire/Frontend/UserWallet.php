<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class UserWallet extends Component
{
    public $user;
    public $transactions;

    public function mount()
    {
        $this->user = Auth::user();
        $this->transactions = $this->user->transactions()->latest()->get();
    }

    public function render()
    {
        return view('livewire.frontend.user-wallet')->layout('livewire.layout.frontend.base');
    }
}

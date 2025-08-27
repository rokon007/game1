<?php

namespace App\Livewire\Backend;

use Livewire\Component;
use App\Models\WelcomeBonusSetting as Wb;

class WelcomeBonusSettings extends Component
{
    public $is_active;
    public $amount;

    public function mount()
    {
        $setting = Wb::first();
        $this->is_active = (bool) $setting->is_active; // 🆕 boolean cast
        $this->amount = $setting->amount;
    }

    public function save()
    {
        $this->validate([
            'amount' => 'required|integer|min:0',
        ]);

        $setting = Wb::first();
        $setting->update([
            'is_active' => $this->is_active,
            'amount' => $this->amount,
        ]);

        $this->is_active = (bool) $setting->is_active;

        session()->flash('success', 'Welcome Bonus Settings Updated!');
    }

    public function render()
    {
        return view('livewire.backend.welcome-bonus-settings')->layout('livewire.backend.base');
    }
}

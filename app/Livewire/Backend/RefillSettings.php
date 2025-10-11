<?php

namespace App\Livewire\Backend;

use Livewire\Component;
use App\Models\RefillSetting;

class RefillSettings extends Component
{
    public $bikash_number;
    public $nagad_number;
    public $rocket_number;
    public $upay_number;
    public $instructions;
    public $is_active = true;

    public function mount()
    {
        $settings = RefillSetting::first();
        if ($settings) {
            $this->bikash_number = $settings->bikash_number;
            $this->nagad_number = $settings->nagad_number;
            $this->rocket_number = $settings->rocket_number;
            $this->upay_number = $settings->upay_number;
            $this->instructions = $settings->instructions;
            $this->is_active = $settings->is_active;
        }
    }

    public function save()
    {
        $this->validate([
            'bikash_number' => 'required|string|max:20',
            'nagad_number' => 'required|string|max:20',
            'rocket_number' => 'required|string|max:20',
            'upay_number' => 'required|string|max:20',
            'instructions' => 'nullable|string',
        ]);

        RefillSetting::updateOrCreate(
            ['id' => 1],
            [
                'bikash_number' => $this->bikash_number,
                'nagad_number' => $this->nagad_number,
                'rocket_number' => $this->rocket_number,
                'upay_number' => $this->upay_number,
                'instructions' => $this->instructions,
                'is_active' => $this->is_active,
            ]
        );

        session()->flash('message', 'Refill settings updated successfully.');
    }

    public function render()
    {
        return view('livewire.backend.refill-settings')->layout('livewire.backend.base');
    }
}

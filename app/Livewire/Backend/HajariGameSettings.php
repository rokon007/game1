<?php

namespace App\Livewire\Backend;

use Livewire\Component;
use App\Models\GameSetting;

class HajariGameSettings extends Component
{
    public $arrangeTimeSeconds;
    public $adminCommissionPercentage;

    public function mount()
    {
        $this->arrangeTimeSeconds = GameSetting::getArrangementTime();
        $this->adminCommissionPercentage = GameSetting::getAdminCommission();
    }

    protected $rules = [
        'arrangeTimeSeconds' => 'required|integer|min:60|max:600', // 1-10 minutes
        'adminCommissionPercentage' => 'required|numeric|min:0|max:50' // 0-50%
    ];

    protected $messages = [
        'arrangeTimeSeconds.required' => 'Arrangement time is required.',
        'arrangeTimeSeconds.integer' => 'Arrangement time must be a number.',
        'arrangeTimeSeconds.min' => 'Arrangement time must be at least 60 seconds.',
        'arrangeTimeSeconds.max' => 'Arrangement time cannot exceed 600 seconds.',
        'adminCommissionPercentage.required' => 'Admin commission is required.',
        'adminCommissionPercentage.numeric' => 'Admin commission must be a number.',
        'adminCommissionPercentage.min' => 'Admin commission cannot be negative.',
        'adminCommissionPercentage.max' => 'Admin commission cannot exceed 50%.'
    ];

    public function saveSettings()
    {
        $this->validate();

        try {
            GameSetting::set('arrange_time_seconds', $this->arrangeTimeSeconds, 'integer', 'Time in seconds for players to arrange their cards');
            GameSetting::set('admin_commission_percentage', $this->adminCommissionPercentage, 'float', 'Admin commission percentage from winner prize');

            session()->flash('success', 'Game settings updated successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    public function resetToDefaults()
    {
        $this->arrangeTimeSeconds = 240; // 4 minutes
        $this->adminCommissionPercentage = 5.0; // 5%

        $this->saveSettings();
        session()->flash('success', 'Settings reset to defaults!');
    }

    public function render()
    {
        return view('livewire.backend.hajari-game-settings')->layout('livewire.backend.base');
    }
}

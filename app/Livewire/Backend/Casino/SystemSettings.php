<?php

namespace App\Livewire\Backend\Casino;

use Livewire\Component;
use App\Models\SystemSetting;
use App\Models\SystemPool;

class SystemSettings extends Component
{
    public $settings = [];
    public $newKey = '';
    public $newValue = '';
    public $poolAmount = 0;

    public function mount()
    {
        $this->loadSettings();
        $this->updatePoolAmount();
    }

    public function updatePoolAmount()
    {
        $pool = SystemPool::first();
        $this->poolAmount = $pool ? $pool->total_collected : 0;
    }

    public function loadSettings()
    {
        $this->settings = SystemSetting::orderBy('key')->get()->toArray();
    }

    public function saveSetting($id, $value)
    {
        $setting = SystemSetting::find($id);
        if ($setting) {
            $setting->value = $value;
            $setting->save();
            $this->dispatch('toast', message: 'Setting updated successfully!');
        }
    }

    public function addSetting()
    {
        $this->validate([
            'newKey' => 'required|string|unique:system_settings,key',
            'newValue' => 'required|string',
        ]);

        SystemSetting::create([
            'key' => $this->newKey,
            'value' => $this->newValue,
        ]);

        $this->newKey = '';
        $this->newValue = '';
        $this->loadSettings();
        $this->dispatch('toast', message: 'New setting added!');
    }

    public function deleteSetting($id)
    {
        SystemSetting::find($id)?->delete();
        $this->loadSettings();
        $this->dispatch('toast', message: 'Setting deleted!');
    }

    public function render()
    {
        return view('livewire.backend.casino.system-settings')->layout('livewire.backend.base');
    }
}

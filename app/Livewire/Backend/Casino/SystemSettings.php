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

    // Quick Stats
    public $stats = [];

    public function mount()
    {
        $this->loadSettings();
        $this->updatePoolAmount();
        $this->calculateStats();
    }

    public function updatePoolAmount()
    {
        $pool = SystemPool::first();
        $this->poolAmount = $pool ? $pool->total_collected : 0;
    }

    public function calculateStats()
    {
        $winChance = (float) SystemSetting::getValue('win_chance_percent', 20);
        $avgMultiplier = (2 + 3 + 4 + 5) / 4; // 3.5

        // Calculate RTP (Return to Player)
        $rtp = ($winChance / 100) * $avgMultiplier;
        $houseEdge = (1 - $rtp) * 100;

        $jackpotLimit = (int) SystemSetting::getValue('jackpot_limit', 100000);
        $jackpotChance = (float) SystemSetting::getValue('jackpot_chance_percent', 0.1);
        $minimumReserve = (int) SystemSetting::getValue('minimum_pool_reserve', 10000);

        $this->stats = [
            'rtp' => round($rtp * 100, 2),
            'house_edge' => round($houseEdge, 2),
            'jackpot_limit' => $jackpotLimit,
            'jackpot_chance' => $jackpotChance,
            'minimum_reserve' => $minimumReserve,
            'available_pool' => max(0, $this->poolAmount - $minimumReserve),
        ];
    }

    public function loadSettings()
    {
        $this->settings = SystemSetting::orderBy('key')->get()->toArray();
    }

    public function saveSetting($id, $value)
    {
        $setting = SystemSetting::find($id);
        if ($setting) {
            // Validate numeric settings
            $numericKeys = [
                'min_bet', 'max_bet', 'win_chance_percent',
                'admin_commission', 'jackpot_limit',
                'jackpot_chance_percent', 'minimum_pool_reserve',
                'max_win_percentage'
            ];

            if (in_array($setting->key, $numericKeys)) {
                if (!is_numeric($value)) {
                    $this->dispatch('toast', message: 'Value must be numeric!', type: 'error');
                    return;
                }

                // Additional validations
                if ($setting->key === 'win_chance_percent' && ($value < 0 || $value > 100)) {
                    $this->dispatch('toast', message: 'Win chance must be between 0-100%', type: 'error');
                    return;
                }

                if ($setting->key === 'admin_commission' && ($value < 0 || $value > 50)) {
                    $this->dispatch('toast', message: 'Commission must be between 0-50%', type: 'error');
                    return;
                }

                if ($setting->key === 'jackpot_chance_percent' && ($value < 0 || $value > 100)) {
                    $this->dispatch('toast', message: 'Jackpot chance must be between 0-100%', type: 'error');
                    return;
                }
            }

            $setting->value = $value;
            $setting->save();

            // Recalculate stats after update
            $this->calculateStats();

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
        $this->calculateStats();
        $this->dispatch('toast', message: 'New setting added!');
    }

    public function deleteSetting($id)
    {
        SystemSetting::find($id)?->delete();
        $this->loadSettings();
        $this->calculateStats();
        $this->dispatch('toast', message: 'Setting deleted!');
    }

    /**
     * Quick preset configurations
     */
    public function applyPreset($preset)
    {
        $presets = [
            'easy' => [
                'win_chance_percent' => '30',
                'admin_commission' => '15',
                'jackpot_chance_percent' => '0.5',
            ],
            'normal' => [
                'win_chance_percent' => '20',
                'admin_commission' => '10',
                'jackpot_chance_percent' => '0.1',
            ],
            'hard' => [
                'win_chance_percent' => '15',
                'admin_commission' => '5',
                'jackpot_chance_percent' => '0.05',
            ],
        ];

        if (!isset($presets[$preset])) return;

        foreach ($presets[$preset] as $key => $value) {
            $setting = SystemSetting::where('key', $key)->first();
            if ($setting) {
                $setting->value = $value;
                $setting->save();
            }
        }

        $this->loadSettings();
        $this->calculateStats();
        $this->dispatch('toast', message: "Preset '{$preset}' applied successfully!");
    }

    public function render()
    {
        return view('livewire.backend.casino.system-settings')->layout('livewire.backend.base');
    }
}

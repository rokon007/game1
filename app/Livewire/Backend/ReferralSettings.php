<?php

namespace App\Livewire\Backend;

use Livewire\Component;
use App\Models\ReferralSetting;

class ReferralSettings extends Component
{
    public $commission_percentage = 0.00;
    public $max_commission_count = 0;

    public function mount()
    {
        $settings = ReferralSetting::first();
        if ($settings) {
            $this->commission_percentage = $settings->commission_percentage;
            $this->max_commission_count = $settings->max_commission_count;
        }
    }

    public function save()
    {
        $this->validate([
            'commission_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_commission_count' => ['required', 'integer', 'min:0'],
        ]);

        ReferralSetting::updateOrCreate(
            ['id' => 1],
            [
                'commission_percentage' => $this->commission_percentage,
                'max_commission_count' => $this->max_commission_count,
            ]
        );

        session()->flash('message', 'Referral settings updated successfully.');
    }

    public function render()
    {
        return view('livewire.backend.referral-settings')->layout('livewire.backend.base');
    }
}

<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use App\Models\AdBanner;
use App\Models\Prize;
use App\Models\User;

class Home extends Component
{
    public $addBanners;
    public $prizes;
    public $cradit;

    public function mount()
    {
        $this->addBanners=AdBanner::where('is_active',true)->get();
        $this->prizes=Prize::where('is_active',true)->get();
        if (auth()->check()) {
            $this->cradit = auth()->user()->credit;
        }
    }

    public function render()
    {
        return view('livewire.frontend.home')->layout('livewire.layout.frontend.base');
    }
}

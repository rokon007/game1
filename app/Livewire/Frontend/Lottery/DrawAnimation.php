<?php

namespace App\Livewire\Frontend\Lottery;

use Livewire\Component;
use App\Models\Lottery;
use App\Models\LotteryResult;

class DrawAnimation extends Component
{
    public $lottery;
    public $showResults = false;
    public $showDrawModal = false;

    public function mount($lottery)
    {
        $this->lottery = Lottery::with(['prizes', 'results.prize', 'results.user'])->findOrFail($lottery);

        // If lottery is completed, show results
        if ($this->lottery->status === 'completed') {
            $this->showResults = true;
        }
    }

    public function checkDrawTime()
    {

    }

    public function render()
    {
        return view('livewire.frontend.lottery.draw-animation')->layout('livewire.layout.frontend.base');
    }
}

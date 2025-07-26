<?php

namespace App\Livewire\Frontend\Lottery;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Lottery;
use App\Models\LotteryResult;
use App\Models\LotteryTicket;
use Illuminate\Support\Facades\Auth;

class ActivLotteries extends Component
{
    use WithPagination;

    public $expandedLotteries = [];

    public function toggleLottery($lotteryId)
    {
        if (in_array($lotteryId, $this->expandedLotteries)) {
            $this->expandedLotteries = array_diff($this->expandedLotteries, [$lotteryId]);
        } else {
            $this->expandedLotteries[] = $lotteryId;
        }
    }

    public function render()
    {
        $userTickets = LotteryTicket::with(['lottery', 'lottery.results' => function($query) {
                    $query->where('user_id', Auth::id());
                }])
                ->where('user_id', Auth::id())
                ->whereHas('lottery', function($query) {
                    $query->where('status', 'active');
                })
                ->orderBy('created_at', 'desc')
                ->get();

        // Group tickets by lottery
        $groupedTickets = $userTickets->groupBy('lottery_id');

        return view('livewire.frontend.lottery.activ-lotteries', [
            'groupedTickets' => $groupedTickets
        ])->layout('livewire.layout.frontend.base');
    }
}

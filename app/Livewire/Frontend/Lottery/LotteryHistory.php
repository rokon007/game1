<?php

namespace App\Livewire\Frontend\Lottery;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Lottery;
use App\Models\LotteryResult;
use App\Models\LotteryTicket;
use Illuminate\Support\Facades\Auth;

class LotteryHistory extends Component
{
    use WithPagination;

    public $activeTab = 'my_tickets';
    public $selectedLottery = null;

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function viewLotteryDetails($lotteryId)
    {
        $this->selectedLottery = Lottery::with(['results.user', 'results.prize'])
            ->findOrFail($lotteryId);
    }

    public function closeDetails()
    {
        $this->selectedLottery = null;
    }

    public function render()
    {
        $data = [];

        switch ($this->activeTab) {
            case 'my_tickets':
                $data['myTickets'] = LotteryTicket::with(['lottery', 'lottery.results' => function($query) {
                    $query->where('user_id', Auth::id());
                }])
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->paginate(10);
                break;

            case 'my_winnings':
                $data['myWinnings'] = LotteryResult::with(['lottery', 'prize'])
                ->where('user_id', Auth::id())
                ->orderBy('drawn_at', 'desc')
                ->paginate(10);
                break;

            case 'all_results':
                $data['allResults'] = Lottery::with(['results.user', 'results.prize'])
                ->where('status', 'completed')
                ->orderBy('draw_date', 'desc')
                ->paginate(10);
                break;
        }

        return view('livewire.frontend.lottery.lottery-history', $data)->layout('livewire.layout.frontend.base');
    }
}

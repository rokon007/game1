<?php

namespace App\Livewire\Backend\Lottery;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Lottery;
use App\Services\LotteryService;
use App\Events\DrawStarted;
use App\Models\User;
use App\Models\LotteryTicket;

class LotteryIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $currentLottery = null;
    public $currentUser = null;
    public $userTickets = [];
    public $purchaserSearch = '';
    public $perPage = 10;

    protected $lotteryService;
    
    protected $listeners = [
        'conductDraw',
        'cancelLottery',
        'showTicketPurchasersModal',
        'showUserTicketsModal'
    ];

    public function boot(LotteryService $lotteryService)
    {
        $this->lotteryService = $lotteryService;
    }

    public function broadcastDrawStart($lotteryId)
    {
        $lottery = Lottery::findOrFail($lotteryId);
        broadcast(new DrawStarted($lottery));
        
        session()->flash('success', 'লাইভ ড্র শুরু করা হয়েছে!');
    }

    public function conductDraw($lotteryId)
    {
        try {
            $lottery = Lottery::findOrFail($lotteryId);
            
            // Check if already completed
            if ($lottery->status === 'completed') {
                session()->flash('error', 'This lottery has already been completed.');
                return;
            }
            
            // Check if results already exist
            if ($lottery->results()->exists()) {
                session()->flash('error', 'Draw results already exist for this lottery.');
                return;
            }
            
            $this->lotteryService->conductDraw($lottery);
            
            session()->flash('success', 'ড্র সফলভাবে সম্পন্ন হয়েছে!');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }
    
    public function cancelLottery($lotteryId)
    {
        try {
            $lottery = Lottery::findOrFail($lotteryId);
            $lottery->update(['status' => 'cancelled']);
            
            session()->flash('success', 'লটারি সফলভাবে বাতিল করা হয়েছে!');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function showTicketPurchasers($lotteryId)
    {
        $this->currentLottery = Lottery::findOrFail($lotteryId);
        $this->dispatch('showModal', id: 'ticketPurchasersModal');
    }

    public function getTicketPurchasersProperty()
    {
        if (!$this->currentLottery) {
            return new LengthAwarePaginator([], 0, 10);
        }

        return LotteryTicket::where('lottery_id', $this->currentLottery->id)
            ->selectRaw('user_id, count(*) as ticket_count')
            ->groupBy('user_id')
            ->with(['user' => function($query) {
                $query->when($this->purchaserSearch, function($q) {
                    $q->where('name', 'like', '%'.$this->purchaserSearch.'%')
                      ->orWhere('mobile', 'like', '%'.$this->purchaserSearch.'%');
                });
            }])
            ->paginate($this->perPage);
    }

    public function updatedPurchaserSearch()
    {
        $this->showTicketPurchasers($this->currentLottery->id);
    }

    public function showUserTickets($lotteryId, $userId)
    {
        $this->currentLottery = Lottery::findOrFail($lotteryId);
        $this->currentUser = User::findOrFail($userId);
        $this->userTickets = LotteryTicket::where('lottery_id', $lotteryId)
            ->where('user_id', $userId)
            ->with(['results.prize'])
            ->get();
            
        $this->dispatch('hideModal', id: 'ticketPurchasersModal');
        $this->dispatch('showModal', id: 'userTicketsModal');
    }


    public function render()
    {
        $lotteries = Lottery::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->with(['prizes', 'tickets'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.backend.lottery.lottery-index', compact('lotteries'))->layout('livewire.backend.base');
    }
}

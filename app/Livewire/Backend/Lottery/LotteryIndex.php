<?php

namespace App\Livewire\Backend\Lottery;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Lottery;
use App\Services\LotteryService;
use App\Events\DrawStarted;

class LotteryIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';

    protected $lotteryService;

    protected $listeners = [
        'conductDraw',
        'cancelLottery'
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

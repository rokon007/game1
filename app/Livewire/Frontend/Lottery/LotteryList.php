<?php

namespace App\Livewire\Frontend\Lottery;

use Livewire\Component;
use App\Models\Lottery;
use App\Services\LotteryService;
use Illuminate\Support\Facades\Auth;

class LotteryList extends Component
{
    public $selectedLottery = null;
    public $ticketQuantity = 1;

    protected $lotteryService;

    public function boot(LotteryService $lotteryService)
    {
        $this->lotteryService = $lotteryService;
    }

    public function selectLottery($lotteryId)
    {
        $this->selectedLottery = Lottery::with('prizes')->findOrFail($lotteryId);
        $this->ticketQuantity = 1;
    }

    public function incrementQuantity()
    {
        if ($this->ticketQuantity < 10) {
            $this->ticketQuantity++;
        }
    }

    public function decrementQuantity()
    {
        if ($this->ticketQuantity > 1) {
            $this->ticketQuantity--;
        }
    }

    public function purchaseTickets()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        try {
            $tickets = $this->lotteryService->purchaseTicket(
                $this->selectedLottery,
                Auth::user(),
                $this->ticketQuantity
            );

            session()->flash('success', 'Tickets purchased successfully!');
            $this->selectedLottery = null;
            $this->dispatch('ticketPurchased');

            return redirect(route('lottery.history'));

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $lotteries = Lottery::where('status', 'active')
            ->where('draw_date', '>', now())
            ->with(['prizes'])
            ->orderBy('draw_date')
            ->get();

        return view('livewire.frontend.lottery.lottery-list', compact('lotteries'))
            ->layout('livewire.layout.frontend.base');
    }
}

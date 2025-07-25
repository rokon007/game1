<?php

namespace App\Livewire\Backend\Lottery;

use Livewire\Component;
use App\Models\Lottery;
use App\Services\LotteryService;
use Illuminate\Support\Facades\Gate;

class Show extends Component
{
    public Lottery $lottery;
    public $activeTab = 'overview';
    public $stats;
    public $perPage = 10;
    public $readyToLoad = false;

    protected $queryString = ['activeTab'];

    public function mount(Lottery $lottery)
    {
        // Authorization check
       // Gate::authorize('view', $lottery);

        $this->lottery = $lottery;
        //$this->stats = app(LotteryService::class)->getLotteryStats($lottery);
    }

    public function loadData()
    {
        $this->readyToLoad = true;
    }

    public function getTicketsProperty()
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->lottery->tickets()
            ->with(['user'])
            ->latest()
            ->paginate($this->perPage);
    }

    public function getResultsProperty()
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->lottery->results()
            ->with(['user', 'prize'])
            ->orderBy('prize_position')
            ->get()
            ->groupBy('prize_position');
    }

    public function render()
    {
        return view('livewire.backend.lottery.show', [
            'tickets' => $this->tickets,
            'groupedResults' => $this->results,
            'totalTickets' => $this->readyToLoad ? $this->lottery->tickets()->count() : 0,
            'totalRevenue' => $this->readyToLoad ? $this->lottery->tickets()->sum('price') : 0,
        ])->layout('livewire.backend.base', [
            'title' => 'লটারি বিস্তারিত - ' . $this->lottery->name,
            'breadcrumbs' => [
                ['title' => 'ড্যাশবোর্ড', 'url' => route('admin.dashboard')],
                ['title' => 'লটারি তালিকা', 'url' => route('admin.lottery.index')],
                ['title' => $this->lottery->name, 'url' => '#'],
            ]
        ]);
    }
}

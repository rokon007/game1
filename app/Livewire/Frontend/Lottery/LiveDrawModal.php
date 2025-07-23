<?php

namespace App\Livewire\Frontend\Lottery;

use Livewire\Component;
use App\Models\Lottery;
use App\Models\LotteryResult;
use App\Services\LotteryService;

class LiveDrawModal extends Component
{
    public $showModal = false;
    public $currentLottery = null;
    public $drawResults = [];
    public $currentPrizeIndex = 0;
    public $isDrawing = false;
    public $drawComplete = false;
    public $currentWinningNumber = '';
    public $showCurrentResult = false;
    public $countdown = 10;
    public $isCountingDown = false;

    protected $listeners = [
        'startLiveDraw' => 'initiateDraw',
        'echo:lottery-channel,DrawStarted' => 'handleDrawStarted'
    ];

    public function handleDrawStarted($event)
    {
        $this->initiateDraw($event['lottery_id']);
    }

    public function initiateDraw($lotteryId)
    {
        $this->currentLottery = Lottery::with(['prizes' => function($query) {
            // Start from lowest prize (highest rank) to highest prize (lowest rank)
            $query->orderBy('rank', 'desc');
        }, 'tickets.user'])->findOrFail($lotteryId);

        $this->showModal = true;
        $this->isDrawing = true;
        $this->drawComplete = false;
        $this->currentPrizeIndex = 0;
        $this->drawResults = [];
        $this->showCurrentResult = false;
        $this->countdown = 10;
        $this->isCountingDown = false;

        // Play draw start sound
        $this->dispatch('playSound', ['type' => 'drawStart']);

        $this->startPrizeDraw();
    }

    public function startPrizeDraw()
    {
        if ($this->currentPrizeIndex >= $this->currentLottery->prizes->count()) {
            $this->completeDraw();
            return;
        }

        $currentPrize = $this->currentLottery->prizes[$this->currentPrizeIndex];
        $this->showCurrentResult = false;
        $this->currentWinningNumber = '';

        // Get the winning number first (but don't show it yet)
        $this->getDrawResult($currentPrize);

        // Play animation sound
        $this->dispatch('playSound', ['type' => 'spinning']);

        // Start the animation
        $this->dispatch('animateDigits', [
            'prizePosition' => $currentPrize->position,
            'prizeAmount' => $currentPrize->amount,
            'winningNumber' => $this->currentWinningNumber
        ]);

        // Show result after 8 seconds of animation
        $this->dispatch('scheduleResultShow');
    }

    private function getDrawResult($prize)
    {
        // Check if there's a pre-selected winner for this prize
        $winningTicketNumber = null;

        if ($this->currentLottery->pre_selected_winners &&
            isset($this->currentLottery->pre_selected_winners[$prize->position])) {
            $winningTicketNumber = $this->currentLottery->pre_selected_winners[$prize->position];
        } else {
            // Random selection from available tickets
            $availableTickets = $this->currentLottery->tickets()
                ->whereNotIn('id', collect($this->drawResults)->pluck('lottery_ticket_id'))
                ->get();

            if ($availableTickets->isNotEmpty()) {
                $winningTicket = $availableTickets->random();
                $winningTicketNumber = $winningTicket->ticket_number;
            }
        }

        if ($winningTicketNumber) {
            $winningTicket = $this->currentLottery->tickets()
                ->where('ticket_number', $winningTicketNumber)
                ->first();

            if ($winningTicket) {
                $result = [
                    'lottery_ticket_id' => $winningTicket->id,
                    'winning_ticket_number' => $winningTicketNumber,
                    'prize_position' => $prize->position,
                    'prize_amount' => $prize->amount,
                    'winner_name' => $winningTicket->user->name,
                    'user_id' => $winningTicket->user_id,
                    'lottery_prize_id' => $prize->id
                ];

                $this->drawResults[] = $result;
                $this->currentWinningNumber = $winningTicketNumber;
            }
        }
    }

    public function showResult()
    {
        $this->showCurrentResult = true;
        $this->dispatch('stopAnimation');

        // Play winner sound
        $this->dispatch('playSound', ['type' => 'winner']);

        // Start countdown for next prize
        $this->startCountdown();
    }

    private function startCountdown()
    {
        $this->isCountingDown = true;
        $this->countdown = 10;

        $this->dispatch('startCountdown');
    }

    public function decrementCountdown()
    {
        $this->countdown--;

        if ($this->countdown <= 0) {
            $this->nextPrize();
        }
    }

    public function nextPrize()
    {
        $this->currentPrizeIndex++;
        $this->isCountingDown = false;
        $this->countdown = 10;

        if ($this->currentPrizeIndex < $this->currentLottery->prizes->count()) {
            $this->startPrizeDraw();
        } else {
            $this->completeDraw();
        }
    }

    private function completeDraw()
    {
        $this->isDrawing = false;
        $this->drawComplete = true;
        $this->isCountingDown = false;

        // Play completion sound
        $this->dispatch('playSound', ['type' => 'complete']);

        // Save results to database and process transactions
        app(LotteryService::class)->saveDrawResults($this->currentLottery, $this->drawResults);

        // Refresh the page data
        $this->dispatch('drawCompleted');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['currentLottery', 'drawResults', 'currentPrizeIndex', 'isDrawing', 'drawComplete', 'showCurrentResult', 'countdown', 'isCountingDown']);

        // Stop all sounds
        $this->dispatch('stopAllSounds');

        // Refresh the page
        return redirect()->to(request()->header('Referer'));
    }

    public function render()
    {
        return view('livewire.frontend.lottery.live-draw-modal');
    }
}

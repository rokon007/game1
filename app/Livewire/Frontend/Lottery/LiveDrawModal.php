<?php

namespace App\Livewire\Frontend\Lottery;

use Livewire\Component;
use App\Models\Lottery;
use App\Services\CentralDrawService;

class LiveDrawModal extends Component
{
    public $showModal = false;
    public $currentLottery = null;
    public $centralDrawResults = [];
    public $currentPrizeIndex = 0;
    public $isDrawing = false;
    public $drawComplete = false;
    public $currentWinningNumber = '';
    public $showCurrentResult = false;
    public $countdown = 10;
    public $isCountingDown = false;
    public $drawSaved = false;
    public $errorMessage = '';
    public $autoCompleteTimer = 300; // 5 minutes in seconds

    protected $listeners = [
        'startLiveDraw' => 'initiateDraw',
        'echo:lottery-channel,DrawStarted' => 'handleDrawStarted'
    ];

    public function mount()
    {
        // Start auto-complete timer when component mounts
        $this->dispatch('startAutoCompleteTimer');
    }

    public function handleDrawStarted($event)
    {
        $this->initiateDraw($event['lottery_id']);
    }

    public function initiateDraw($lotteryId)
    {
        try {
            $this->currentLottery = Lottery::with(['prizes' => function($query) {
                $query->orderBy('rank', 'desc');
            }, 'tickets.user'])->findOrFail($lotteryId);

            // Reset states
            $this->drawSaved = false;
            $this->errorMessage = '';

            // Get centralized draw results
            $centralDrawService = app(CentralDrawService::class);

            // Check if results are already saved
            if ($centralDrawService->isDrawResultsSaved($this->currentLottery)) {
                $this->errorMessage = 'This lottery draw has already been completed.';
                return;
            }

            $this->centralDrawResults = $centralDrawService->startCentralDraw($this->currentLottery);

            if (empty($this->centralDrawResults)) {
                $this->errorMessage = 'No draw results could be generated.';
                return;
            }

            // Calculate dynamic auto-complete timer based on prize count
            $prizeCount = count($this->centralDrawResults);
            $dynamicDuration = $centralDrawService->calculateDrawDuration($prizeCount);
            $this->autoCompleteTimer = $dynamicDuration * 60; // Convert to seconds

            $this->showModal = true;
            $this->isDrawing = true;
            $this->drawComplete = false;
            $this->currentPrizeIndex = 0;
            $this->showCurrentResult = false;
            $this->countdown = 10;
            $this->isCountingDown = false;

            // Play draw start sound
            $this->dispatch('playSound', ['type' => 'drawStart']);

            // Start auto-complete countdown with dynamic timer
            $this->dispatch('startAutoCompleteCountdown', ['duration' => $this->autoCompleteTimer]);

            $this->startPrizeDraw();

        } catch (\Exception $e) {
            $this->errorMessage = 'Error starting draw: ' . $e->getMessage();
            \Log::error('LiveDrawModal initiateDraw error: ' . $e->getMessage());
        }
    }

    public function decrementAutoCompleteTimer()
    {
        $this->autoCompleteTimer--;

        if ($this->autoCompleteTimer <= 0 && !$this->drawComplete && !$this->drawSaved) {
            // Auto-complete the draw
            $this->autoCompleteDraw();
        }
    }

    public function autoCompleteDraw()
    {
        $this->isDrawing = false;
        $this->drawComplete = true;
        $this->isCountingDown = false;

        // Stop all animations and sounds
        $this->dispatch('stopAllSounds');
        $this->dispatch('stopAnimation');

        // Save results automatically
        if (!$this->drawSaved && !empty($this->centralDrawResults) && $this->currentLottery) {
            try {
                $centralDrawService = app(CentralDrawService::class);
                $saved = $centralDrawService->saveCentralDrawResults($this->currentLottery);

                if ($saved) {
                    $this->drawSaved = true;
                    session()->flash('success', 'Draw auto-completed successfully!');
                } else {
                    $this->errorMessage = 'Draw results could not be saved automatically.';
                }
            } catch (\Exception $e) {
                $this->errorMessage = 'Error auto-completing draw: ' . $e->getMessage();
                \Log::error('LiveDrawModal autoCompleteDraw error: ' . $e->getMessage());
            }
        }

        $this->dispatch('drawCompleted');
    }

    public function startPrizeDraw()
    {
        // Check if we've shown all prizes
        if ($this->currentPrizeIndex >= count($this->centralDrawResults)) {
            $this->completeDraw();
            return;
        }

        $currentResult = $this->centralDrawResults[$this->currentPrizeIndex];
        $this->showCurrentResult = false;
        $this->currentWinningNumber = $currentResult['winning_ticket_number'] ?? '';

        if (empty($this->currentWinningNumber)) {
            $this->errorMessage = 'Invalid winning number for current prize.';
            return;
        }

        // Play animation sound
        $this->dispatch('playSound', ['type' => 'spinning']);

        // Start the animation
        $this->dispatch('animateDigits', [
            'prizePosition' => $currentResult['prize_position'],
            'prizeAmount' => $currentResult['prize_amount'],
            'winningNumber' => $this->currentWinningNumber
        ]);

        // Show result after 8 seconds of animation
        $this->dispatch('scheduleResultShow');
    }

    public function showResult()
    {
        $this->showCurrentResult = true;
        $this->dispatch('stopAnimation');

        // Play winner sound
        $this->dispatch('playSound', ['type' => 'winner']);

        // Check if this is the last prize
        if ($this->currentPrizeIndex >= count($this->centralDrawResults) - 1) {
            // This is the last prize, complete the draw after a short delay
            $this->dispatch('scheduleDrawCompletion');
        } else {
            // Start countdown for next prize
            $this->startCountdown();
        }
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

        // Start the next prize draw
        $this->startPrizeDraw();
    }

    public function completeDraw()
    {
        $this->isDrawing = false;
        $this->drawComplete = true;
        $this->isCountingDown = false;

        // Play completion sound
        $this->dispatch('playSound', ['type' => 'complete']);

        // Save results to database (only once and only if not already saved)
        if (!$this->drawSaved && !empty($this->centralDrawResults) && $this->currentLottery) {
            try {
                $centralDrawService = app(CentralDrawService::class);
                $saved = $centralDrawService->saveCentralDrawResults($this->currentLottery);

                if ($saved) {
                    $this->drawSaved = true;
                    session()->flash('success', 'Draw completed successfully!');
                } else {
                    $this->errorMessage = 'Draw results could not be saved. They may have already been saved.';
                }
            } catch (\Exception $e) {
                $this->errorMessage = 'Error saving draw results: ' . $e->getMessage();
                \Log::error('LiveDrawModal completeDraw error: ' . $e->getMessage());
            }
        }

        // Refresh the page data
        $this->dispatch('drawCompleted');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset([
            'currentLottery',
            'centralDrawResults',
            'currentPrizeIndex',
            'isDrawing',
            'drawComplete',
            'showCurrentResult',
            'countdown',
            'isCountingDown',
            'drawSaved',
            'errorMessage',
            'autoCompleteTimer'
        ]);

        // Stop all sounds
        $this->dispatch('stopAllSounds');

        // Refresh the page
        return redirect()->to(request()->header('Referer'));
    }

    public function getDynamicTimerInfo()
    {
        $prizeCount = count($this->centralDrawResults);
        $minutes = floor($this->autoCompleteTimer / 60);
        $seconds = $this->autoCompleteTimer % 60;

        return [
            'prize_count' => $prizeCount,
            'total_minutes' => floor($this->autoCompleteTimer / 60),
            'remaining_time' => $minutes . ':' . str_pad($seconds, 2, '0', STR_PAD_LEFT),
            'progress_percentage' => $prizeCount > 0 ? (($this->currentPrizeIndex + 1) / $prizeCount) * 100 : 0
        ];
    }

    public function render()
    {
        return view('livewire.frontend.lottery.live-draw-modal');
    }
}

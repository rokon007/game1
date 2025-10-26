<?php

namespace App\Livewire\Frontend\CrashGame;

use App\Models\CrashGame;
use App\Models\CrashBet;
use App\Services\CrashGameService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;
use Exception;

class CrashGameComponent extends Component
{
    public ?CrashGame $currentGame = null;
    public ?CrashBet $userBet = null;
    public float $betAmount = 5;
    public float $currentMultiplier = 1.00;
    public string $gameStatus = 'waiting';
    public array $recentGames = [];
    public string $errorMessage = '';
    public string $successMessage = '';
    public int $waitingPlayerCount = 0;
    public int $runningPlayerCount = 0;
    public bool $isFirstLoad = true;
    public  $srartWCount = true;

    // Countdown timestamp for JavaScript
    public string $countdownTimestamp = '';

    protected CrashGameService $gameService;

    public function boot(CrashGameService $gameService): void
    {
        $this->gameService = $gameService;
    }

    public function mount(): void
    {
        $this->loadCurrentGame();
        $this->loadRecentGames();
        $this->updateCountdownTimestamp();
        // শুধু প্রথম লোডে waiting player count generate করুন
        if ($this->isFirstLoad) {
            $this->waitingPlayerCount = rand(1025, 10712);
            $this->isFirstLoad = false;
        }
    }

    /**
     * Generate random player counts
     */
    public function generatePlayerCounts(): void
    {
        //dd($this->srartWCount);
        // if($this->srartWCount){
        //     $this->waitingPlayerCount = rand(1025, 10712);
        // }

        if($this->gameStatus !== 'waiting'){
            $this->waitingPlayerCount = rand(1025, 10712);
        }
    }

    /**
     * Increase waiting player count
     */
    #[On('increaseWaitingPlayers')]
    public function increaseWaitingPlayers(): void
    {
        if ($this->gameStatus === 'waiting') {
            $this->srartWCount=false;
            $increase = rand(50, 200); // কম রেঞ্জ দিয়ে ধীরে ধীরে বাড়ানো
            $this->waitingPlayerCount += $increase;
        }
        dd($this->srartWCount);
    }

    /**
     * Decrease running player count
     */
    #[On('decreaseRunningPlayers')]
    public function decreaseRunningPlayers(): void
    {
        if ($this->gameStatus === 'running' && $this->runningPlayerCount > 50) {
            $decrease = rand(5, 250);
            $this->runningPlayerCount = max(50, $this->runningPlayerCount - $decrease);
        }
    }

    /**
     * Reset player counts for new game
     */
    #[On('resetPlayerCounts')]
    public function resetPlayerCounts(): void
    {
        $this->generatePlayerCounts();
    }

    /**
     * Update countdown timestamp for JavaScript
     */
    public function updateCountdownTimestamp(): void
    {
        $this->countdownTimestamp = now()->addSeconds(10)->format('Y-m-d H:i:s');
    }

    /**
     * Increase bet amount by 5
     */
    public function increaseBetAmount(): void
    {
        $this->betAmount += 5;
    }

    /**
     * Decrease bet amount by 5
     */
    public function decreaseBetAmount(): void
    {
        if ($this->betAmount > 5) {
            $this->betAmount -= 5;
        } else {
            $this->betAmount = 1;
        }
    }

    /**
     * Place bet
     */
    public function placeBet(): void
    {
        $this->resetMessages();

        try {
            $user = Auth::user();

            if (!$user) {
                $this->errorMessage = 'Please login to place a bet';
                return;
            }

            // Check if user already has a bet in current game
            if ($this->currentGame && $this->userBet) {
                $this->errorMessage = 'You already have a bet in this game';
                return;
            }

            // Create new game if none exists
            if (!$this->currentGame || $this->currentGame->isCrashed()) {
                $this->currentGame = $this->gameService->createGame();
                $this->updateCountdownTimestamp();
            }

            // Place bet
            $this->userBet = $this->gameService->placeBet(
                $user,
                $this->currentGame,
                $this->betAmount
            );

            $this->successMessage = 'Bet placed successfully!';
            $this->dispatch('betPlaced');

        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    /**
     * Cashout
     */
    public function cashout(): void
    {
        $this->resetMessages();

        try {
            if (!$this->userBet || !$this->userBet->isPlaying()) {
                $this->errorMessage = 'No active bet to cashout';
                return;
            }

            $this->gameService->cashout($this->userBet, $this->currentMultiplier);

            $winAmount = $this->userBet->bet_amount * $this->currentMultiplier;
            $this->successMessage = sprintf(
                'Cashed out at %.2fx! Won: ৳%.2f',
                $this->currentMultiplier,
                $winAmount
            );

            $this->userBet->refresh();
            $this->dispatch('cashedOut');

        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function pollGameData(): void
    {
        $gameData = cache()->get('crash_game_current');

        if ($gameData) {
            $previousStatus = $this->gameStatus;
            $this->currentMultiplier = $gameData['multiplier'];
            $this->gameStatus = $gameData['status'];

            // Update countdown timestamp when game enters waiting state
            // if ($this->gameStatus === 'waiting' && $previousStatus !== 'waiting') {

            //         $this->generatePlayerCounts();
            //         $this->dispatch('startWaitingIncrease');
            //         $this->srartWCount=false;

            // }



            // Start running simulation when game starts
            if ($this->gameStatus === 'running' && $previousStatus !== 'running') {
                $this->runningPlayerCount = $this->waitingPlayerCount;
                $this->dispatch('startRunningDecrease');
            }

            // Dispatch event to frontend if crashed
            if ($gameData['status'] === 'crashed') {
                $this->dispatch('gameCrashed', crashPoint: $gameData['crash_point']);
                $this->srartWCount=true;
            }
        }

        $this->loadCurrentGame();
        $this->loadRecentGames();
    }

    /**
     * Refresh component (called by wire:poll)
     */
    public function refreshGameState(): void
    {
         if ($this->gameStatus === 'waiting'){
                $this->srartWCount=false;
                $increase = rand(50, 200); // কম রেঞ্জ দিয়ে ধীরে ধীরে বাড়ানো
                $this->waitingPlayerCount += $increase;
             }
        $this->pollGameData();
    }

    /**
     * Load current game
     */
    private function loadCurrentGame(): void
    {
        $this->currentGame = $this->gameService->getCurrentGame();

        if ($this->currentGame && Auth::check()) {
            $this->userBet = CrashBet::where('crash_game_id', $this->currentGame->id)
                ->where('user_id', Auth::id())
                ->first();
        }

        if ($this->currentGame) {
            if ($this->currentGame->isRunning()) {
                $this->gameStatus = 'running';
            } elseif ($this->currentGame->isPending()) {
                $this->gameStatus = 'waiting';
            } else {
                $this->gameStatus = 'crashed';
            }
        }
    }

    /**
     * Load recent games
     */
    private function loadRecentGames(): void
    {
        $this->recentGames = CrashGame::where('status', 'crashed')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function($game) {
                return [
                    'id' => $game->id,
                    'crash_point' => $game->crash_point,
                    'created_at' => $game->created_at->format('H:i:s'),
                ];
            })
            ->toArray();
    }

    /**
     * Reset messages
     */
    private function resetMessages(): void
    {
        $this->errorMessage = '';
        $this->successMessage = '';
    }

    /**
     * Refresh component
     */
    #[On('refreshGame')]
    public function refresh(): void
    {
        $this->loadCurrentGame();
        $this->loadRecentGames();
    }

    public function render()
    {
        return view('livewire.frontend.crash-game.crash-game-component')->layout('livewire.layout.frontend.base');
    }
}

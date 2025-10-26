<?php

namespace App\Livewire\Backend\CrashGame;

use App\Models\CrashGame;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class CrashGameStatus extends Component
{
    public $isRunning = false;
    public $currentGame;
    public $recentGames = [];

    public function mount()
    {
        $this->checkGameStatus();
        $this->loadCurrentGame();
        $this->loadRecentGames();
    }

    public function checkGameStatus()
    {
        $this->isRunning = Cache::get('crash_game_running', false);
    }

    public function loadCurrentGame()
    {
        $this->currentGame = CrashGame::whereIn('status', ['pending', 'running'])
            ->latest()
            ->first();
    }

    public function loadRecentGames()
    {
        $this->recentGames = CrashGame::with('bets')
            ->where('status', 'crashed')
            ->latest()
            ->limit(10)
            ->get();
    }

    public function startGame()
    {
        if ($this->isRunning) {
            session()->flash('error', 'Game is already running!');
            return;
        }

        // Start the game process in background
        exec('php ' . base_path('artisan') . ' crash:run > /dev/null 2>&1 &');

        Cache::put('crash_game_running', true, 3600);
        $this->isRunning = true;

        session()->flash('message', 'Game started successfully!');
    }

    public function stopGame()
    {
        if (!$this->isRunning) {
            session()->flash('error', 'Game is not running!');
            return;
        }

        Cache::put('crash_game_stop', true, 60);
        Cache::forget('crash_game_running');
        $this->isRunning = false;

        session()->flash('message', 'Game stopped successfully!');
    }

    public function restartGame()
    {
        $this->stopGame();

        // Wait for 2 seconds
        sleep(2);

        $this->startGame();

        session()->flash('message', 'Game restarted successfully!');
    }

    public function render()
    {
        return view('livewire.backend.crash-game.crash-game-status')->layout('livewire.backend.base');
    }
}

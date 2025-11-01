<?php
// app/Livewire/Backend/CrashGame/CrashGameDashboard.php - FIXED

namespace App\Livewire\Backend\CrashGame;

use App\Models\CrashGame;
use App\Models\CrashBet;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class CrashGameDashboard extends Component
{
    use WithPagination;

    public $dateFrom;
    public $dateTo;
    public $total_bet_pool;

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    /**
     * ✅ Refresh method for wire:poll
     */
    public function refreshData()
    {
        // This method will be called by wire:poll
        // Livewire will automatically re-render
    }

    public function render()
    {
        $stats = $this->getStatistics();
        $games = $this->getGames();
        $topWinners = $this->getTopWinners();
        $recentBets = $this->getRecentBets();
        $pool = $this->poolData();

        return view('livewire.backend.crash-game.crash-game-dashboard', [
            'stats' => $stats,
            'games' => $games,
            'topWinners' => $topWinners,
            'recentBets' => $recentBets,
            'pool' => $pool,
        ])->layout('livewire.backend.base');
    }

    /**
     * ✅ FIXED: Pool data calculation
     */
    private function poolData()
    {
        // Current running/pending game এর pool
        $currentGame = CrashGame::whereIn('status', ['pending', 'running'])
            ->latest()
            ->first();

        if ($currentGame) {
            $totalBetPool = $currentGame->total_bet_pool;
        } else {
            // যদি কোন active game না থাকে, শেষ game এর rollover দেখাও
            $lastGame = CrashGame::where('status', 'crashed')
                ->latest()
                ->first();

            $totalBetPool = $lastGame ? $lastGame->rollover_to_next : 0;
        }

        return [
            'total_bet_pool' => $totalBetPool,
            'current_game_id' => $currentGame ? $currentGame->id : null,
            'current_status' => $currentGame ? $currentGame->status : 'No active game',
        ];
    }

    /**
     * ✅ FIXED: Statistics calculation
     */
    private function getStatistics()
    {
        $query = CrashGame::whereBetween('created_at', [
            $this->dateFrom . ' 00:00:00',
            $this->dateTo . ' 23:59:59'
        ]);

        $totalGames = $query->count();

        // ✅ Total bets থেকে previous_rollover বাদ দিয়ে শুধু actual bets
        $totalBets = CrashGame::whereBetween('created_at', [
            $this->dateFrom . ' 00:00:00',
            $this->dateTo . ' 23:59:59'
        ])->sum('current_round_bets'); // শুধু current round bets

        // ✅ Total payouts = শুধু profit (bet amount নয়)
        $totalPayouts = CrashGame::whereBetween('created_at', [
            $this->dateFrom . ' 00:00:00',
            $this->dateTo . ' 23:59:59'
        ])->sum('total_payout');

        // ✅ House profit calculation
        $totalCommission = CrashGame::whereBetween('created_at', [
            $this->dateFrom . ' 00:00:00',
            $this->dateTo . ' 23:59:59'
        ])->sum('admin_commission_amount');

        $totalRollover = CrashGame::whereBetween('created_at', [
            $this->dateFrom . ' 00:00:00',
            $this->dateTo . ' 23:59:59'
        ])->sum('rollover_to_next');

        // House profit = Total bets - Total payouts - Total rollover
        // অথবা: House profit = Commission + (Remaining - Rollover)
        $houseProfit = $totalBets - $totalPayouts - $totalRollover;

        $houseEdge = $totalBets > 0 ? ($houseProfit / $totalBets) * 100 : 0;

        return [
            'total_games' => $totalGames,
            'total_bets' => $totalBets,
            'total_payouts' => $totalPayouts,
            'total_commission' => $totalCommission,
            'total_rollover' => $totalRollover,
            'house_profit' => $houseProfit,
            'house_edge' => $houseEdge,
            'avg_crash_point' => $query->avg('crash_point') ?? 0,
        ];
    }

    /**
     * ✅ FIXED: Games list with accurate data
     */
    public function getGames()
    {
        return CrashGame::whereBetween('created_at', [
                $this->dateFrom . ' 00:00:00',
                $this->dateTo . ' 23:59:59'
            ])
            ->latest()
            ->paginate(20);
    }

    private function getTopWinners()
    {
        return CrashBet::select('user_id', DB::raw('SUM(profit) as total_profit'))
            ->with('user:id,name')
            ->whereHas('game', function($q) {
                $q->whereBetween('created_at', [
                    $this->dateFrom . ' 00:00:00',
                    $this->dateTo . ' 23:59:59'
                ]);
            })
            ->where('status', 'won')
            ->groupBy('user_id')
            ->orderByDesc('total_profit')
            ->limit(10)
            ->get();
    }

    private function getRecentBets()
    {
        return CrashBet::with(['user:id,name', 'game:id,crash_point,status'])
            ->latest()
            ->limit(20)
            ->get();
    }
}

<?php

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

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function render()
    {
        $stats = $this->getStatistics();
        $games = $this->getGames();
        $topWinners = $this->getTopWinners();
        $recentBets = $this->getRecentBets();

        return view('livewire.backend.crash-game.crash-game-dashboard', [
            'stats' => $stats,
            'games' => $games,
            'topWinners' => $topWinners,
            'recentBets' => $recentBets,
        ])->layout('livewire.backend.base');
    }

    private function getStatistics()
    {
        $query = CrashGame::whereBetween('created_at', [
            $this->dateFrom . ' 00:00:00',
            $this->dateTo . ' 23:59:59'
        ]);

        $totalGames = $query->count();
        $totalBets = CrashBet::whereHas('game', function($q) {
            $q->whereBetween('created_at', [
                $this->dateFrom . ' 00:00:00',
                $this->dateTo . ' 23:59:59'
            ]);
        })->sum('bet_amount');

        $totalPayouts = CrashBet::whereHas('game', function($q) {
            $q->whereBetween('created_at', [
                $this->dateFrom . ' 00:00:00',
                $this->dateTo . ' 23:59:59'
            ]);
        })->where('status', 'won')->sum('profit');

        $houseProfit = $totalBets - $totalPayouts;
        $houseEdge = $totalBets > 0 ? ($houseProfit / $totalBets) * 100 : 0;

        return [
            'total_games' => $totalGames,
            'total_bets' => $totalBets,
            'total_payouts' => $totalPayouts,
            'house_profit' => $houseProfit,
            'house_edge' => $houseEdge,
            'avg_crash_point' => $query->avg('crash_point') ?? 0,
        ];
    }

    public function getGames()
    {
        return CrashGame::with(['bets' => function($query) {
                $query->select('crash_game_id',
                    DB::raw('COUNT(*) as bet_count'),
                    DB::raw('SUM(bet_amount) as total_bet'),
                    DB::raw('SUM(CASE WHEN status = "won" THEN profit ELSE 0 END) as total_payout')
                )
                ->groupBy('crash_game_id');
            }])
            ->whereBetween('created_at', [
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

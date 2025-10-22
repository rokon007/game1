<?php


namespace App\Console\Commands;

use App\Models\CrashGame;
use App\Models\CrashBet;
use App\Models\CrashGameArchive;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupOldCrashGames extends Command
{
    protected $signature = 'crash:cleanup {--minutes=30 : Delete data older than X minutes} {--dry-run : Show what would be deleted without actually deleting}';
    protected $description = 'Delete old crash games and bets data to keep database clean';

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');
        $dryRun = $this->option('dry-run');

        $this->info("🧹 Starting cleanup process...");
        $this->info("⏰ Processing data older than {$minutes} minutes");

        if ($dryRun) {
            $this->warn("🔍 DRY RUN MODE - No data will be deleted");
        }

        $cutoffTime = now()->subMinutes($minutes);
        $this->info("📅 Cutoff time: {$cutoffTime->format('Y-m-d H:i:s')}");

        // Step 1: Archive data
        $this->info("📦 Archiving daily statistics...");
        $archiveResult = $this->archiveDailyStats($cutoffTime);

        if ($archiveResult['success'] > 0) {
            $this->info("✅ Successfully archived {$archiveResult['success']} days");
        }
        if ($archiveResult['skipped'] > 0) {
            $this->info("⏭️  Skipped {$archiveResult['skipped']} already archived days");
        }

        // Step 2: Count records for deletion
        $oldGamesCount = CrashGame::where('created_at', '<', $cutoffTime)
            ->where('status', 'crashed')
            ->count();

        $oldBetsCount = CrashBet::whereHas('game', function($query) use ($cutoffTime) {
            $query->where('created_at', '<', $cutoffTime)
                  ->where('status', 'crashed');
        })->count();

        $this->info("📊 Found {$oldGamesCount} old games and {$oldBetsCount} old bets for deletion");

        if ($oldGamesCount === 0 && $oldBetsCount === 0) {
            $this->info("✅ No old data to clean up!");
            return Command::SUCCESS;
        }

        if ($dryRun) {
            $this->warn("Would delete:");
            $this->table(
                ['Type', 'Count'],
                [
                    ['Games', $oldGamesCount],
                    ['Bets', $oldBetsCount],
                ]
            );
            return Command::SUCCESS;
        }

        // Step 3: Perform deletion
        try {
            DB::transaction(function () use ($cutoffTime, $oldGamesCount, $oldBetsCount) {
                // Get old game IDs
                $oldGameIds = CrashGame::where('created_at', '<', $cutoffTime)
                    ->where('status', 'crashed')
                    ->pluck('id');

                if ($oldGameIds->isEmpty()) {
                    return;
                }

                // Delete old bets first
                $deletedBets = CrashBet::whereIn('crash_game_id', $oldGameIds)->delete();

                // Delete old games
                $deletedGames = CrashGame::whereIn('id', $oldGameIds)->delete();

                // Log the cleanup
                Log::info("Crash Game Cleanup Completed", [
                    'deleted_games' => $deletedGames,
                    'deleted_bets' => $deletedBets,
                    'cutoff_time' => $cutoffTime->toDateTimeString(),
                    'archive_period' => 'daily_stats_saved',
                ]);

                $this->info("🗑️  Deleted {$deletedBets} old bets");
                $this->info("🗑️  Deleted {$deletedGames} old games");
            });

            $this->info("🎉 Cleanup completed successfully!");
            $this->showArchiveStats();

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Error during cleanup: {$e->getMessage()}");
            Log::error("Crash Game Cleanup Failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Archive daily statistics
     */
    private function archiveDailyStats($cutoffTime): array
    {
        $result = ['success' => 0, 'skipped' => 0, 'failed' => 0];

        try {
            // Get unique dates from old games
            $dates = CrashGame::where('created_at', '<', $cutoffTime)
                ->where('status', 'crashed')
                ->selectRaw('DATE(created_at) as game_date')
                ->groupBy('game_date')
                ->orderBy('game_date')
                ->pluck('game_date');

            foreach ($dates as $date) {
                try {
                    // Check if already archived
                    if (CrashGameArchive::where('archive_date', $date)->exists()) {
                        $result['skipped']++;
                        continue;
                    }

                    // Get games for this date
                    $games = CrashGame::whereDate('created_at', $date)
                        ->where('status', 'crashed')
                        ->get();

                    if ($games->isEmpty()) {
                        continue;
                    }

                    $gameIds = $games->pluck('id');

                    // Get all bets for these games
                    $bets = CrashBet::whereIn('crash_game_id', $gameIds)->get();

                    // Calculate statistics
                    $totalBetAmount = $bets->sum('bet_amount');
                    $totalPayout = $bets->where('status', 'won')->sum(function($bet) {
                        return $bet->bet_amount * $bet->multiplier;
                    });

                    $houseProfit = $totalBetAmount - $totalPayout;
                    $winRate = $bets->count() > 0 ?
                        ($bets->where('status', 'won')->count() / $bets->count()) * 100 : 0;

                    // Create archive record
                    CrashGameArchive::create([
                        'archive_date' => $date,
                        'total_games' => $games->count(),
                        'total_bets' => $bets->count(),
                        'total_bet_amount' => $totalBetAmount,
                        'total_payout' => $totalPayout,
                        'house_profit' => $houseProfit,
                        'average_crash_point' => $games->avg('crash_point') ?? 0,
                        'highest_crash_point' => $games->max('crash_point') ?? 0,
                        'lowest_crash_point' => $games->min('crash_point') ?? 0,
                        'additional_stats' => [
                            'total_players' => $bets->unique('user_id')->count(),
                            'win_rate' => round($winRate, 2),
                            'total_won_bets' => $bets->where('status', 'won')->count(),
                            'total_lost_bets' => $bets->where('status', 'lost')->count(),
                            'average_bet_amount' => $bets->avg('bet_amount') ?? 0,
                            'max_bet_amount' => $bets->max('bet_amount') ?? 0,
                            'min_bet_amount' => $bets->min('bet_amount') ?? 0,
                        ],
                    ]);

                    $this->info("📦 Archived data for {$date} - Games: {$games->count()}, Bets: {$bets->count()}");
                    $result['success']++;

                } catch (\Exception $e) {
                    $this->warn("⚠️ Failed to archive data for {$date}: {$e->getMessage()}");
                    $result['failed']++;
                }
            }

        } catch (\Exception $e) {
            $this->error("❌ Archive process failed: {$e->getMessage()}");
        }

        return $result;
    }

    /**
     * Show archive statistics
     */
    private function showArchiveStats(): void
    {
        $totalArchives = CrashGameArchive::count();
        $latestArchive = CrashGameArchive::latest('archive_date')->first();

        $this->newLine();
        $this->info("📊 Archive Statistics:");

        $statsData = [
            ['Total Archived Days', $totalArchives],
            ['Latest Archive Date', $latestArchive ? $latestArchive->archive_date : 'None'],
        ];

        if ($latestArchive) {
            $statsData = array_merge($statsData, [
                ['Games on Latest', $latestArchive->total_games],
                ['Bets on Latest', $latestArchive->total_bets],
                ['Total Bet Amount', number_format($latestArchive->total_bet_amount, 8)],
                ['House Profit', number_format($latestArchive->house_profit, 8)],
            ]);
        }

        $this->table(['Metric', 'Value'], $statsData);
    }
}

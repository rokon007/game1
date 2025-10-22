<?php

namespace App\Listeners;

use App\Events\CrashGameStarted;
use App\Events\CrashGameCrashed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendCrashGameNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        if ($event instanceof CrashGameStarted) {
            $this->handleGameStarted($event);
        } elseif ($event instanceof CrashGameCrashed) {
            $this->handleGameCrashed($event);
        }
    }

    /**
     * Handle game started event
     */
    protected function handleGameStarted(CrashGameStarted $event): void
    {
        Log::info("Crash Game #{$event->game->id} started", [
            'game_id' => $event->game->id,
            'crash_point' => $event->game->crash_point,
            'total_bets' => $event->game->bets->count(),
        ]);

        // এখানে আপনি notification পাঠাতে পারেন
        // যেমন: Pusher, WebSocket, Database Notification ইত্যাদি

        // Example: Broadcast to all users (optional)
        // broadcast(new GameStartedNotification($event->game))->toOthers();
    }

    /**
     * Handle game crashed event
     */
    protected function handleGameCrashed(CrashGameCrashed $event): void
    {
        $game = $event->game;

        Log::info("Crash Game #{$game->id} crashed at {$game->crash_point}x", [
            'game_id' => $game->id,
            'crash_point' => $game->crash_point,
            'total_bets' => $game->total_bet_amount,
            'total_payouts' => $game->total_payout,
            'house_profit' => $game->total_bet_amount - $game->total_payout,
        ]);

        // Notify winners
        $this->notifyWinners($game);

        // Notify big wins (optional)
        $this->notifyBigWins($game);

        // Update statistics cache
        $this->updateStatisticsCache($game);
    }

    /**
     * Notify winners
     */
    protected function notifyWinners($game): void
    {
        $winners = $game->bets()->where('status', 'won')->with('user')->get();

        foreach ($winners as $bet) {
            // Database notification (Laravel built-in)
            // $bet->user->notify(new CrashGameWinNotification($bet));

            // অথবা custom notification logic
            Log::info("User #{$bet->user_id} won ৳{$bet->profit} in game #{$game->id}");
        }
    }

    /**
     * Notify big wins (10x or more)
     */
    protected function notifyBigWins($game): void
    {
        $bigWinThreshold = config('crash-game.notifications.big_win_threshold', 10);

        if (!config('crash-game.notifications.big_win', true)) {
            return;
        }

        $bigWins = $game->bets()
            ->where('status', 'won')
            ->where('cashout_at', '>=', $bigWinThreshold)
            ->with('user')
            ->get();

        foreach ($bigWins as $bet) {
            Log::info("🎉 BIG WIN! User #{$bet->user_id} cashed out at {$bet->cashout_at}x winning ৳{$bet->profit}");

            // Broadcast big win to all users (optional)
            // broadcast(new BigWinNotification($bet))->toOthers();
        }
    }

    /**
     * Update statistics cache
     */
    protected function updateStatisticsCache($game): void
    {
        $cacheKey = 'crash_game_daily_stats_' . now()->format('Y-m-d');

        $stats = cache()->get($cacheKey, [
            'total_games' => 0,
            'total_bets' => 0,
            'total_payouts' => 0,
            'house_profit' => 0,
        ]);

        $stats['total_games']++;
        $stats['total_bets'] += $game->total_bet_amount;
        $stats['total_payouts'] += $game->total_payout;
        $stats['house_profit'] += ($game->total_bet_amount - $game->total_payout);

        cache()->put($cacheKey, $stats, now()->endOfDay());
    }

    /**
     * Handle a job failure.
     */
    public function failed($event, \Throwable $exception): void
    {
        Log::error('Crash Game Notification Failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

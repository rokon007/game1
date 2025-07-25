<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lottery;
use App\Services\CentralDrawService;

class CompleteStuckDraws extends Command
{
    protected $signature = 'lottery:complete-stuck-draws';
    protected $description = 'Complete draws that are stuck in progress';

    public function handle()
    {
        $this->info('🔍 Checking for stuck draws...');

        $centralDrawService = app(CentralDrawService::class);

        // Find lotteries that might be stuck
        $potentiallyStuckLotteries = Lottery::where('status', 'active')
            ->where('draw_date', '<=', now()->subMinutes(1)) // At least 1 minute past draw time
            ->get();

        if ($potentiallyStuckLotteries->isEmpty()) {
            $this->info('✅ No potentially stuck lotteries found.');
            return;
        }

        $this->info("Found {$potentiallyStuckLotteries->count()} potentially stuck lotteries.");
        $completedCount = 0;

        foreach ($potentiallyStuckLotteries as $lottery) {
            try {
                $this->info("Checking lottery: {$lottery->name} (ID: {$lottery->id})");

                // Refresh lottery data
                $lottery->refresh();

                // Skip if already completed
                if ($lottery->status === 'completed') {
                    $this->info("Lottery {$lottery->name} is already completed.");
                    continue;
                }

                // Skip if results already exist
                if ($lottery->results()->exists()) {
                    $this->info("Results exist for {$lottery->name}. Marking as completed.");
                    $lottery->update(['status' => 'completed']);
                    continue;
                }

                // Check draw status
                $status = $centralDrawService->getDrawStatus($lottery);

                if ($status && $status['status'] === 'in_progress') {
                    $estimatedDuration = $status['estimated_duration'] ?? 5;
                    $startedAt = \Carbon\Carbon::parse($status['started_at']);
                    $shouldCompleteAt = \Carbon\Carbon::parse($status['auto_complete_at']);

                    $this->info("Draw in progress for {$lottery->name}:");
                    $this->info("  Started: {$startedAt->format('Y-m-d H:i:s')}");
                    $this->info("  Should complete: {$shouldCompleteAt->format('Y-m-d H:i:s')}");
                    $this->info("  Duration: {$estimatedDuration} minutes");

                    // Check if it's really stuck (past completion time + 2 minute buffer)
                    if (now()->greaterThan($shouldCompleteAt->addMinutes(2))) {
                        $this->info("🚨 Draw is stuck for {$lottery->name}. Attempting to complete...");

                        $completed = $centralDrawService->saveCentralDrawResults($lottery);

                        if ($completed) {
                            $this->info("✅ Successfully completed stuck draw for: {$lottery->name}");
                            $completedCount++;
                        } else {
                            $this->error("❌ Failed to complete stuck draw for: {$lottery->name}");
                        }
                    } else {
                        $remainingMinutes = now()->diffInMinutes($shouldCompleteAt, false);
                        $this->info("⏳ Draw for {$lottery->name} is still within expected time. {$remainingMinutes} minutes remaining.");
                    }
                } else {
                    // No status found, check if it's a missed draw
                    $timeSinceDrawDate = now()->diffInMinutes($lottery->draw_date);
                    $prizeCount = $lottery->prizes()->count();
                    $expectedDuration = $centralDrawService->calculateDrawDuration($prizeCount);

                    if ($lottery->tickets()->count() === 0) {
                        $this->info("No tickets sold for {$lottery->name}. Cancelling.");
                        $lottery->update(['status' => 'cancelled']);
                    } elseif ($timeSinceDrawDate > $expectedDuration + 5) {
                        $this->info("🔄 Running missed draw for: {$lottery->name}");
                        $this->info("  Time since draw date: {$timeSinceDrawDate} minutes");
                        $this->info("  Expected duration: {$expectedDuration} minutes");
                        $this->info("  Prize count: {$prizeCount}");

                        try {
                            $drawResults = $centralDrawService->startCentralDraw($lottery);
                            $saved = $centralDrawService->saveCentralDrawResults($lottery);

                            if ($saved) {
                                $this->info("✅ Completed missed draw for: {$lottery->name}");
                                $completedCount++;
                            } else {
                                $this->error("❌ Failed to complete missed draw for: {$lottery->name}");
                            }
                        } catch (\Exception $e) {
                            $this->error("❌ Exception during missed draw for {$lottery->name}: " . $e->getMessage());
                        }
                    } else {
                        $this->info("⏳ Draw for {$lottery->name} might still be starting. Waiting...");
                    }
                }

            } catch (\Exception $e) {
                $this->error("❌ Error processing {$lottery->name}: " . $e->getMessage());
                \Log::error("CompleteStuckDraws error for lottery {$lottery->id}: " . $e->getMessage());
            }
        }

        $this->info("✅ Completed {$completedCount} stuck draws.");
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lottery;
use App\Services\CentralDrawService;
use Illuminate\Support\Facades\Log;

class CompleteStuckDraws extends Command
{
    protected $signature = 'lottery:complete-stuck-draws';
    protected $description = 'Complete draws that are stuck in progress';

    public function handle()
    {
        $this->info('Checking for stuck draws...');

        $centralDrawService = app(CentralDrawService::class);

        // Find lotteries that should be completed but are still active
        $stuckLotteries = Lottery::where('status', 'active')
            ->where('draw_date', '<=', now()->subMinutes(2)) // At least 2 minutes past draw time
            ->get();

        $completedCount = 0;

        foreach ($stuckLotteries as $lottery) {
            try {
                // Check if this draw is stuck
                $status = $centralDrawService->getDrawStatus($lottery);

                if ($status && $status['status'] === 'in_progress') {
                    $estimatedDuration = $status['estimated_duration'] ?? 5;
                    $startedAt = \Carbon\Carbon::parse($status['started_at']);
                    $shouldCompleteAt = \Carbon\Carbon::parse($status['auto_complete_at']);

                    $this->info("Found draw in progress for lottery: {$lottery->name}");
                    $this->info("Started at: {$startedAt->format('Y-m-d H:i:s')}");
                    $this->info("Should complete at: {$shouldCompleteAt->format('Y-m-d H:i:s')}");
                    $this->info("Estimated duration: {$estimatedDuration} minutes");

                    // Check if it's really stuck (past the estimated completion time + buffer)
                    if (now()->greaterThan($shouldCompleteAt->addMinutes(2))) {
                        $this->info("Draw is stuck for lottery: {$lottery->name}");

                        // Try to auto-complete it
                        $completed = $centralDrawService->checkAndAutoCompleteDraw($lottery);

                        if ($completed) {
                            $this->info("Successfully completed stuck draw for: {$lottery->name}");
                            $completedCount++;
                        } else {
                            // If auto-complete fails, try to save results directly
                            $drawResults = $centralDrawService->getCentralDrawResults($lottery);
                            if ($drawResults) {
                                $saved = $centralDrawService->saveCentralDrawResults($lottery);
                                if ($saved) {
                                    $this->info("Force completed draw for: {$lottery->name}");
                                    $completedCount++;
                                }
                            }
                        }
                    } else {
                        $remainingMinutes = now()->diffInMinutes($shouldCompleteAt, false);
                        $this->info("Draw for {$lottery->name} is still within expected time. {$remainingMinutes} minutes remaining.");
                    }
                } else {
                    // No cached status, check if we need to run the draw
                    $timeSinceDrawDate = now()->diffInMinutes($lottery->draw_date);

                    if (!$lottery->results()->exists() && $lottery->tickets()->count() > 0) {
                        // Calculate expected duration for this lottery
                        $prizeCount = $lottery->prizes()->count();
                        $expectedDuration = $centralDrawService->calculateDrawDuration($prizeCount);

                        // Only consider it missed if enough time has passed
                        if ($timeSinceDrawDate > $expectedDuration + 5) {
                            $this->info("Running missed draw for: {$lottery->name} (Prize count: {$prizeCount}, Expected duration: {$expectedDuration} minutes)");

                            $drawResults = $centralDrawService->startCentralDraw($lottery);
                            $saved = $centralDrawService->saveCentralDrawResults($lottery);

                            if ($saved) {
                                $this->info("Completed missed draw for: {$lottery->name}");
                                $completedCount++;
                            }
                        } else {
                            $this->info("Draw for {$lottery->name} might still be in progress. Waiting...");
                        }
                    } else if ($lottery->tickets()->count() === 0) {
                        // No tickets sold, cancel the lottery
                        $lottery->update(['status' => 'cancelled']);
                        $this->info("Cancelled lottery with no tickets: {$lottery->name}");
                    }
                }

            } catch (\Exception $e) {
                $this->error("Error processing stuck draw for {$lottery->name}: " . $e->getMessage());
                \Log::error("CompleteStuckDraws error for lottery {$lottery->id}: " . $e->getMessage());
            }
        }

        $this->info("Completed {$completedCount} stuck draws.");
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lottery;
use App\Services\CentralDrawService;
use App\Events\DrawStarted;

class RunScheduledDraws extends Command
{
    protected $signature = 'lottery:run-draws';
    protected $description = 'Run scheduled lottery draws';

    public function handle()
    {
        $this->info('Starting scheduled draws check...');

        // Find lotteries that need to be drawn
        $lotteries = Lottery::where('status', 'active')
            ->where('auto_draw', true)
            ->where('draw_date', '<=', now())
            ->get();

        if ($lotteries->isEmpty()) {
            $this->info('No lotteries found for scheduled draw.');
            return;
        }

        $this->info("Found {$lotteries->count()} lotteries for processing.");

        foreach ($lotteries as $lottery) {
            try {
                $this->info("Processing lottery: {$lottery->name} (ID: {$lottery->id})");

                // First check: Is lottery still active?
                $lottery->refresh(); // Get fresh data from database
                if ($lottery->status !== 'active') {
                    $this->info("Lottery {$lottery->name} is no longer active (Status: {$lottery->status}). Skipping.");
                    continue;
                }

                // Second check: Are results already saved?
                if ($lottery->results()->exists()) {
                    $this->info("Lottery {$lottery->name} already has results. Marking as completed.");
                    $lottery->update(['status' => 'completed']);
                    continue;
                }

                // Third check: Are there any tickets sold?
                $ticketCount = $lottery->tickets()->count();
                if ($ticketCount === 0) {
                    $this->info("No tickets sold for lottery {$lottery->name}. Cancelling.");
                    $lottery->update(['status' => 'cancelled']);
                    continue;
                }

                $this->info("Lottery {$lottery->name} has {$ticketCount} tickets sold.");

                $centralDrawService = app(CentralDrawService::class);

                // Fourth check: Is draw already completed in service?
                if ($centralDrawService->isDrawResultsSaved($lottery)) {
                    $this->info("Lottery {$lottery->name} already completed in service. Skipping.");
                    continue;
                }

                // Fifth check: Is draw already in progress?
                $drawStatus = $centralDrawService->getDrawStatus($lottery);
                if ($drawStatus && $drawStatus['status'] === 'in_progress') {
                    $this->info("Draw already in progress for lottery {$lottery->name}. Checking if it needs completion.");

                    // Check if it's time to auto-complete
                    $autoCompleteTime = \Carbon\Carbon::parse($drawStatus['auto_complete_at']);
                    if (now()->greaterThan($autoCompleteTime)) {
                        $this->info("Auto-completing overdue draw for lottery {$lottery->name}");
                        $completed = $centralDrawService->saveCentralDrawResults($lottery);
                        if ($completed) {
                            $this->info("Successfully auto-completed draw for lottery {$lottery->name}");
                        } else {
                            $this->error("Failed to auto-complete draw for lottery {$lottery->name}");
                        }
                    } else {
                        $remainingMinutes = now()->diffInMinutes($autoCompleteTime, false);
                        $this->info("Draw for {$lottery->name} is still in progress. {$remainingMinutes} minutes remaining.");
                    }
                    continue;
                }

                // Start new draw process
                $this->info("Starting new draw process for lottery {$lottery->name}");

                // Generate draw results
                $drawResults = $centralDrawService->startCentralDraw($lottery);

                if (empty($drawResults)) {
                    $this->error("No draw results generated for lottery {$lottery->name}");
                    continue;
                }

                $prizeCount = count($drawResults);
                $estimatedDuration = $centralDrawService->calculateDrawDuration($prizeCount);

                $this->info("Lottery {$lottery->name} has {$prizeCount} prizes. Estimated duration: {$estimatedDuration} minutes.");

                // Broadcast that draw is starting (for live viewers)
                try {
                    broadcast(new DrawStarted($lottery));
                    $this->info("Draw started broadcast sent for lottery: {$lottery->name}");
                } catch (\Exception $e) {
                    $this->error("Failed to broadcast draw start for {$lottery->name}: " . $e->getMessage());
                    // Continue anyway, broadcast failure shouldn't stop the draw
                }

                // Wait for the estimated duration to allow live viewers
                $waitSeconds = $estimatedDuration * 60; // Convert to seconds
                $this->info("Waiting {$estimatedDuration} minutes ({$waitSeconds} seconds) for live viewers...");

                // Wait in small chunks and check status periodically
                $chunkSize = 10; // 10 seconds chunks
                $totalWaited = 0;

                while ($totalWaited < $waitSeconds) {
                    $remainingTime = $waitSeconds - $totalWaited;
                    $currentChunk = min($chunkSize, $remainingTime);

                    sleep($currentChunk);
                    $totalWaited += $currentChunk;

                    // Refresh lottery status from database
                    $lottery->refresh();

                    // Check if draw was completed manually during wait
                    if ($lottery->status === 'completed') {
                        $this->info("Draw was completed manually for lottery: {$lottery->name}. Stopping wait.");
                        break;
                    }

                    // Check if results were saved during wait
                    if ($lottery->results()->exists()) {
                        $this->info("Results were saved during wait for lottery: {$lottery->name}. Stopping wait.");
                        $lottery->update(['status' => 'completed']);
                        break;
                    }

                    // Log progress every minute
                    if ($totalWaited % 60 === 0) {
                        $waitedMinutes = $totalWaited / 60;
                        $totalMinutes = $waitSeconds / 60;
                        $this->info("Waited {$waitedMinutes}/{$totalMinutes} minutes for lottery: {$lottery->name}");
                    }
                }

                // Final check before saving results
                $lottery->refresh();

                if ($lottery->status === 'completed') {
                    $this->info("Lottery {$lottery->name} was already completed during wait period.");
                    continue;
                }

                if ($lottery->results()->exists()) {
                    $this->info("Results already exist for lottery {$lottery->name}. Marking as completed.");
                    $lottery->update(['status' => 'completed']);
                    continue;
                }

                // Save the results automatically
                $this->info("Attempting to save results for lottery {$lottery->name}");
                $saved = $centralDrawService->saveCentralDrawResults($lottery);

                if ($saved) {
                    $this->info("✅ Draw completed successfully for lottery: {$lottery->name}");
                } else {
                    $this->error("❌ Failed to save draw results for lottery: {$lottery->name}");

                    // Try one more time with fresh data
                    $lottery->refresh();
                    if ($lottery->status !== 'completed' && !$lottery->results()->exists()) {
                        $this->info("Retrying to save results for lottery {$lottery->name}");
                        $retryResults = $centralDrawService->getCentralDrawResults($lottery);
                        if ($retryResults) {
                            $retrySaved = $centralDrawService->saveCentralDrawResults($lottery);
                            if ($retrySaved) {
                                $this->info("✅ Retry successful for lottery: {$lottery->name}");
                            } else {
                                $this->error("❌ Retry failed for lottery: {$lottery->name}");
                            }
                        }
                    }
                }

            } catch (\Exception $e) {
                $this->error("❌ Exception while processing lottery {$lottery->name}: " . $e->getMessage());
                $this->error("Stack trace: " . $e->getTraceAsString());

                // Log the error for debugging
                \Log::error("Scheduled draw error for lottery {$lottery->id}: " . $e->getMessage(), [
                    'lottery_id' => $lottery->id,
                    'lottery_name' => $lottery->name,
                    'exception' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("✅ Scheduled draws processing completed.");
    }
}

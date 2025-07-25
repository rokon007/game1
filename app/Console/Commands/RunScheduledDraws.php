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
        $lotteries = Lottery::where('status', 'active')
            ->where('auto_draw', true)
            ->where('draw_date', '<=', now())
            ->get();

        foreach ($lotteries as $lottery) {
            try {
                $this->info("Processing lottery: {$lottery->name}");

                // Check if draw is already in progress or completed
                if ($lottery->status !== 'active') {
                    $this->info("Lottery {$lottery->name} is not active. Skipping.");
                    continue;
                }

                // Check if results already exist
                if ($lottery->results()->exists()) {
                    $this->info("Lottery {$lottery->name} already has results. Marking as completed.");
                    $lottery->update(['status' => 'completed']);
                    continue;
                }

                // Check if there are any tickets sold
                if ($lottery->tickets()->count() === 0) {
                    $this->info("No tickets sold for lottery {$lottery->name}. Cancelling.");
                    $lottery->update(['status' => 'cancelled']);
                    continue;
                }

                $centralDrawService = app(CentralDrawService::class);

                // Check if draw results are already saved
                if ($centralDrawService->isDrawResultsSaved($lottery)) {
                    $this->info("Lottery {$lottery->name} already completed. Skipping.");
                    continue;
                }

                // Start the central draw process
                $drawResults = $centralDrawService->startCentralDraw($lottery);

                if (empty($drawResults)) {
                    $this->error("No draw results generated for lottery {$lottery->name}");
                    continue;
                }

                $prizeCount = count($drawResults);
                $estimatedDuration = $centralDrawService->calculateDrawDuration($prizeCount);

                $this->info("Lottery {$lottery->name} has {$prizeCount} prizes. Estimated duration: {$estimatedDuration} minutes.");

                // Broadcast that draw is starting (for live viewers)
                broadcast(new DrawStarted($lottery));
                $this->info("Draw started broadcast sent for lottery: {$lottery->name}");

                // Dynamic wait time based on prize count
                $waitSeconds = $estimatedDuration * 60; // Convert to seconds
                $this->info("Waiting {$estimatedDuration} minutes for live viewers...");

                // Wait in chunks to allow for interruption if needed
                $chunkSize = 30; // 30 seconds chunks
                $totalWaited = 0;

                while ($totalWaited < $waitSeconds) {
                    $remainingTime = $waitSeconds - $totalWaited;
                    $currentChunk = min($chunkSize, $remainingTime);

                    sleep($currentChunk);
                    $totalWaited += $currentChunk;

                    // Check if draw was completed manually during wait
                    $lottery->refresh();
                    if ($lottery->status === 'completed') {
                        $this->info("Draw was completed manually for lottery: {$lottery->name}");
                        break;
                    }

                    $this->info("Waited {$totalWaited}/{$waitSeconds} seconds for lottery: {$lottery->name}");
                }

                // After the wait period, save the results automatically if not already done
                if ($lottery->status === 'active') {
                    $saved = $centralDrawService->saveCentralDrawResults($lottery);

                    if ($saved) {
                        $this->info("Draw completed successfully for lottery: {$lottery->name}");
                    } else {
                        $this->error("Failed to save draw results for lottery: {$lottery->name}");
                    }
                }

            } catch (\Exception $e) {
                $this->error("Failed to process lottery {$lottery->name}: " . $e->getMessage());

                // Log the error for debugging
                \Log::error("Scheduled draw error for lottery {$lottery->id}: " . $e->getMessage());
            }
        }

        $this->info("Scheduled draws processing completed.");
    }
}

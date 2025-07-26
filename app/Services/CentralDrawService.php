<?php

namespace App\Services;

use App\Models\Lottery;
use App\Models\LotteryTicket;
use App\Models\LotteryResult;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Exception;

class CentralDrawService
{
    public function startCentralDraw(Lottery $lottery): array
    {
        // Check if draw is already in progress
        $cacheKey = "lottery_draw_{$lottery->id}";
        $statusKey = "lottery_draw_status_{$lottery->id}";

        // If results already exist in cache, return them
        if (Cache::has($cacheKey)) {
            $existingResults = Cache::get($cacheKey);
            \Log::info("Returning existing cached results for lottery {$lottery->id}");
            return $existingResults;
        }

        if ($lottery->status !== 'active') {
            throw new Exception('Lottery is not active.');
        }

        // Double check if results already exist in database
        if ($lottery->results()->exists()) {
            throw new Exception('Draw results already exist in database.');
        }

        $tickets = $lottery->tickets;
        if ($tickets->isEmpty()) {
            throw new Exception('No tickets sold.');
        }

        // Generate all draw results at once
        $drawResults = $this->generateDrawResults($lottery);

        // Calculate dynamic completion time based on number of prizes
        $prizeCount = count($drawResults);
        $baseTime = 2; // 2 minutes base time
        $timePerPrize = 1; // 1 minute per prize
        $maxTime = 15; // Maximum 15 minutes

        $totalMinutes = min($baseTime + ($prizeCount * $timePerPrize), $maxTime);

        // Cache the results with extended expiry
        Cache::put($cacheKey, $drawResults, now()->addMinutes($totalMinutes + 10));

        // Set draw status with dynamic timestamp
        $statusData = [
            'status' => 'in_progress',
            'started_at' => now()->toISOString(),
            'auto_complete_at' => now()->addMinutes($totalMinutes)->toISOString(),
            'prize_count' => $prizeCount,
            'estimated_duration' => $totalMinutes,
            'lottery_id' => $lottery->id,
            'lottery_name' => $lottery->name
        ];

        Cache::put($statusKey, $statusData, now()->addMinutes($totalMinutes + 10));

        \Log::info("Started central draw for lottery {$lottery->id}", $statusData);

        return $drawResults;
    }

    private function generateDrawResults(Lottery $lottery): array
    {
        $tickets = $lottery->tickets;
        $prizes = $lottery->prizes()->orderBy('rank', 'desc')->get(); // Start from lowest prize
        $results = [];
        $usedTicketIds = [];

        foreach ($prizes as $prize) {
            $winningTicket = null;

            // Check pre-selected winners first
            if ($lottery->pre_selected_winners &&
                isset($lottery->pre_selected_winners[$prize->position])) {

                $preSelectedTicketNumber = $lottery->pre_selected_winners[$prize->position];
                $winningTicket = $tickets->where('ticket_number', $preSelectedTicketNumber)
                                       ->whereNotIn('id', $usedTicketIds)
                                       ->first();
            }

            // Random selection if no pre-selected winner or already used
            if (!$winningTicket) {
                $availableTickets = $tickets->whereNotIn('id', $usedTicketIds);
                if ($availableTickets->isNotEmpty()) {
                    $winningTicket = $availableTickets->random();
                }
            }

            if ($winningTicket) {
                $usedTicketIds[] = $winningTicket->id;

                $results[] = [
                    'lottery_ticket_id' => $winningTicket->id,
                    'winning_ticket_number' => $winningTicket->ticket_number,
                    'prize_position' => $prize->position,
                    'prize_amount' => $prize->amount,
                    'winner_name' => $winningTicket->user->name,
                    'winner_unique_id' => $winningTicket->user->unique_id,
                    'user_id' => $winningTicket->user_id,
                    'lottery_prize_id' => $prize->id,
                    'rank' => $prize->rank
                ];
            }
        }

        return $results;
    }

    public function saveCentralDrawResults(Lottery $lottery): bool
    {
        $cacheKey = "lottery_draw_{$lottery->id}";
        $statusKey = "lottery_draw_status_{$lottery->id}";

        \Log::info("Attempting to save central draw results for lottery {$lottery->id}");

        // Check if already saved to prevent duplicate saves
        if ($lottery->status === 'completed') {
            \Log::info("Lottery {$lottery->id} already completed");
            return false; // Already completed
        }

        // Check if results already exist in database
        if ($lottery->results()->exists()) {
            \Log::info("Results already exist for lottery {$lottery->id}");
            // Mark as completed if not already
            if ($lottery->status !== 'completed') {
                $lottery->update(['status' => 'completed']);
            }
            // Clear cache
            Cache::forget($cacheKey);
            Cache::forget($statusKey);
            return false; // Results already saved
        }

        $drawResults = Cache::get($cacheKey);

        if (!$drawResults) {
            \Log::error("No cached results found for lottery {$lottery->id}");
            return false; // No cached results
        }

        $admin = User::where('role', 'admin')->first();

        try {
            DB::transaction(function () use ($lottery, $drawResults, $admin, $cacheKey, $statusKey) {
                \Log::info("Starting transaction to save results for lottery {$lottery->id}");

                foreach ($drawResults as $resultData) {
                    // Double check if this specific result already exists
                    $existingResult = LotteryResult::where('lottery_id', $lottery->id)
                        ->where('lottery_prize_id', $resultData['lottery_prize_id'])
                        ->first();

                    if ($existingResult) {
                        \Log::info("Result already exists for prize {$resultData['prize_position']} in lottery {$lottery->id}");
                        continue; // Skip if already exists
                    }

                    // Save result to database
                    $result = LotteryResult::create([
                        'lottery_id' => $lottery->id,
                        'lottery_prize_id' => $resultData['lottery_prize_id'],
                        'lottery_ticket_id' => $resultData['lottery_ticket_id'],
                        'user_id' => $resultData['user_id'],
                        'winning_ticket_number' => $resultData['winning_ticket_number'],
                        'prize_amount' => $resultData['prize_amount'],
                        'drawn_at' => now()
                    ]);

                    \Log::info("Saved result for prize {$resultData['prize_position']} in lottery {$lottery->id}");

                    // Add credit to winner
                    $winner = User::find($resultData['user_id']);
                    if ($winner) {
                        $winner->addCredit(
                            $resultData['prize_amount'],
                            "Lottery prize - {$resultData['prize_position']} - {$lottery->name}"
                        );
                        \Log::info("Added credit {$resultData['prize_amount']} to user {$winner->id}");
                    }

                    // Deduct credit from admin
                    if ($admin) {
                        $admin->deductCredit(
                            $resultData['prize_amount'],
                            "Lottery prize payment - {$resultData['prize_position']} - {$lottery->name}"
                        );
                        \Log::info("Deducted credit {$resultData['prize_amount']} from admin");
                    }
                }

                // Mark lottery as completed
                $lottery->update(['status' => 'completed']);
                \Log::info("Marked lottery {$lottery->id} as completed");

                // Clear cache after successful save
                Cache::forget($cacheKey);
                Cache::forget($statusKey);
                \Log::info("Cleared cache for lottery {$lottery->id}");
            });

            \Log::info("Successfully saved all results for lottery {$lottery->id}");
            return true;

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error("Error saving central draw results for lottery {$lottery->id}: " . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function getCentralDrawResults(Lottery $lottery): ?array
    {
        $cacheKey = "lottery_draw_{$lottery->id}";
        return Cache::get($cacheKey);
    }

    public function isDrawResultsSaved(Lottery $lottery): bool
    {
        return $lottery->status === 'completed' && $lottery->results()->exists();
    }

    public function clearDrawCache(Lottery $lottery): void
    {
        $cacheKey = "lottery_draw_{$lottery->id}";
        $statusKey = "lottery_draw_status_{$lottery->id}";
        Cache::forget($cacheKey);
        Cache::forget($statusKey);
        \Log::info("Cleared draw cache for lottery {$lottery->id}");
    }

    public function getDrawStatus(Lottery $lottery): ?array
    {
        $statusKey = "lottery_draw_status_{$lottery->id}";
        return Cache::get($statusKey);
    }

    public function checkAndAutoCompleteDraw(Lottery $lottery): bool
    {
        $status = $this->getDrawStatus($lottery);

        if (!$status || $status['status'] !== 'in_progress') {
            return false;
        }

        // Check if auto complete time has passed
        $autoCompleteTime = \Carbon\Carbon::parse($status['auto_complete_at']);
        if (now()->greaterThan($autoCompleteTime)) {
            \Log::info("Auto completing draw for lottery {$lottery->id} due to timeout");
            return $this->saveCentralDrawResults($lottery);
        }

        return false;
    }

    public function calculateDrawDuration(int $prizeCount): int
    {
        // Base time: 2 minutes
        // Per prize: 1 minute (8 seconds animation + 10 seconds countdown + buffer)
        // Maximum: 15 minutes to prevent extremely long draws

        $baseTime = 2;
        $timePerPrize = 1;
        $maxTime = 15;

        return min($baseTime + ($prizeCount * $timePerPrize), $maxTime);
    }
}

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

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        if ($lottery->status !== 'active') {
            throw new Exception('Lottery is not active.');
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

        // Cache the results
        Cache::put($cacheKey, $drawResults, now()->addMinutes($totalMinutes + 5));

        // Set draw status with dynamic timestamp
        Cache::put($statusKey, [
            'status' => 'in_progress',
            'started_at' => now(),
            'auto_complete_at' => now()->addMinutes($totalMinutes),
            'prize_count' => $prizeCount,
            'estimated_duration' => $totalMinutes
        ], now()->addMinutes($totalMinutes + 5));

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
        // Check if already saved to prevent duplicate saves
        if ($lottery->status === 'completed') {
            return false; // Already completed
        }

        // Check if results already exist in database
        if ($lottery->results()->exists()) {
            return false; // Results already saved
        }

        // Define cache key here
        $cacheKey = "lottery_draw_{$lottery->id}";
        $statusKey = "lottery_draw_status_{$lottery->id}";
        $drawResults = Cache::get($cacheKey);

        if (!$drawResults) {
            return false; // No cached results
        }

        $admin = User::where('role', 'admin')->first();

        try {
            DB::transaction(function () use ($lottery, $drawResults, $admin, $cacheKey, $statusKey) {
                foreach ($drawResults as $resultData) {
                    // Double check if this specific result already exists
                    $existingResult = LotteryResult::where('lottery_id', $lottery->id)
                        ->where('lottery_prize_id', $resultData['lottery_prize_id'])
                        ->first();

                    if ($existingResult) {
                        continue; // Skip if already exists
                    }

                    // Save result to database
                    LotteryResult::create([
                        'lottery_id' => $lottery->id,
                        'lottery_prize_id' => $resultData['lottery_prize_id'],
                        'lottery_ticket_id' => $resultData['lottery_ticket_id'],
                        'user_id' => $resultData['user_id'],
                        'winning_ticket_number' => $resultData['winning_ticket_number'],
                        'prize_amount' => $resultData['prize_amount'],
                        'drawn_at' => now()
                    ]);

                    // Add credit to winner
                    $winner = User::find($resultData['user_id']);
                    if ($winner) {
                        $winner->addCredit(
                            $resultData['prize_amount'],
                            "Lottery prize - {$resultData['prize_position']} - {$lottery->name}"
                        );
                    }

                    // Deduct credit from admin
                    if ($admin) {
                        $admin->deductCredit(
                            $resultData['prize_amount'],
                            "Lottery prize payment - {$resultData['prize_position']} - {$lottery->name}"
                        );
                    }
                }

                // Mark lottery as completed
                $lottery->update(['status' => 'completed']);

                // Clear cache after successful save
                Cache::forget($cacheKey);
                Cache::forget($statusKey);
            });

            return true;
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error saving central draw results: ' . $e->getMessage());
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
        if (now()->greaterThan($status['auto_complete_at'])) {
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

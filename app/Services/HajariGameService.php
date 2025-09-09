<?php

namespace App\Services;

use App\Models\HajariGame;
use App\Models\HajariGameParticipant;
use App\Models\HajariGameMove;
use App\Models\Transaction;
use App\Models\User;
use App\Models\GameSetting;
use App\Events\HajariGameOver;
use App\Events\GameWinner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HajariGameService
{
    //updated
    public function checkGameEndConditions(HajariGame $game): bool
    {
        // শুরুতেই এই চেকটি গেম শেষ হয়েছে কিনা
        if ($game->status === HajariGame::STATUS_COMPLETED) {
            return true;
        }

        // প্রথমে চেক করুন সকল প্লেয়ারের কার্ড শেষ হয়েছে কিনা
        $allCardsFinished = $game->participants()
            ->where('status', HajariGameParticipant::STATUS_PLAYING)
            ->get()
            ->every(function ($participant) {
                return is_array($participant->cards) && count($participant->cards) === 0;
            });

        if (!$allCardsFinished) {
            return false; // কার্ড থাকলে গেম শেষ হবেনা
        }

        // সর্বোচ্চ পয়েন্ট প্রাপ্ত প্লেয়ার খুঁজুন
        $maxPoints = $game->participants()
            ->where('status', HajariGameParticipant::STATUS_PLAYING)
            ->max('total_points');

        // ১০০০ বা তার বেশি পয়েন্ট না থাকলে গেম শেষ হবেনা
        if ($maxPoints === null || $maxPoints < 1000) {
            return false;
        }

        // সর্বোচ্চ পয়েন্ট প্রাপ্ত প্লেয়ারদের তালিকা করুন
        $potentialWinners = $game->participants()
            ->where('status', HajariGameParticipant::STATUS_PLAYING)
            ->where('total_points', '>=', 1000) // শুধুমাত্র ১০০০+ পয়েন্ট প্রাপ্ত প্লেয়ার
            ->where('total_points', $maxPoints) // সর্বোচ্চ পয়েন্ট প্রাপ্ত
            ->get();

        if ($potentialWinners->isEmpty()) {
            return false;
        }

        // যদি একাধিক প্লেয়ারের একই সর্বোচ্চ পয়েন্ট থাকে
        if ($potentialWinners->count() > 1) {
            // যে আগে সর্বোচ্চ পয়েন্ট অর্জন করেছে তাকে বিজয়ী করুন
            $winner = $this->determineEarliestMaxPointsWinner($potentialWinners, $game);
        } else {
            $winner = $potentialWinners->first();
        }

        // Winner null কিনা চেক করুন
        if (!$winner) {
            Log::error('No winner found in checkGameEndConditions', ['game_id' => $game->id]);
            return false;
        }

        $this->endGame($game, $winner);
        return true;
    }

    private function determineEarliestMaxPointsWinner($winners, HajariGame $game)
    {
        $earliestWinner = null;
        $earliestTime = null;

        foreach ($winners as $winner) {
            $timeReachedMaxPoints = $this->getTimeWhenReachedMaxPoints($winner, $game);

            if ($timeReachedMaxPoints === null) {
                continue; // সময় না পাওয়া গেলে skip করুন
            }

            if ($earliestTime === null || $timeReachedMaxPoints < $earliestTime) {
                $earliestTime = $timeReachedMaxPoints;
                $earliestWinner = $winner;
            }
        }

        // যদি কোনো বিজয়ী না পাওয়া যায়, প্রথম বিজয়ী রিটার্ন করুন
        return $earliestWinner ?? $winners->first();
    }

    private function getTimeWhenReachedMaxPoints($participant, HajariGame $game)
    {
        try {
            // প্লেয়ার কখন সর্বোচ্চ পয়েন্ট অর্জন করেছে তা নির্ধারণ করুন
            $roundScores = $participant->round_scores ?? [];
            $maxPoints = $participant->total_points;
            $currentPoints = 0;

            foreach ($roundScores as $roundScore) {
                $currentPoints += $roundScore['points'];

                // যখন পয়েন্ট সর্বোচ্চ পয়েন্টে পৌঁছায়
                if ($currentPoints >= $maxPoints) {
                    // এই রাউন্ডের শেষ মুভের সময় রিটার্ন করুন
                    $roundMove = HajariGameMove::where('hajari_game_id', $game->id)
                        ->where('round', $roundScore['round'])
                        ->orderBy('created_at', 'desc')
                        ->first();

                    return $roundMove ? $roundMove->created_at : now();
                }
            }

            return now(); // fallback
        } catch (\Exception $e) {
            Log::error('Error in getTimeWhenReachedMaxPoints: ' . $e->getMessage());
            return now(); // error fallback
        }
    }

    public function endGame(HajariGame $game, HajariGameParticipant $winner)
    {
        // আগে চেক করুন
        if ($game->status === HajariGame::STATUS_COMPLETED) {
            Log::warning('Game already ended: ' . $game->id);
            return;
        }

        // যদি winner null হয়, তবে বিজয়ী নির্ধারণ করুন
        if (!$winner) {
            $winner = $this->calculateWinner($game);
        }
        DB::transaction(function () use ($game, $winner) {
            $game->update([
                'status' => HajariGame::STATUS_COMPLETED,
                'winner_id' => $winner->user_id
            ]);

            $game->participants()->update([
                'status' => HajariGameParticipant::STATUS_FINISHED
            ]);

            $finalScores = $game->participants()
                ->with('user')
                ->get()
                ->map(function ($participant) {
                    return [
                        'user_id' => $participant->user_id,
                        'name' => $participant->user->name,
                        'total_points' => $participant->total_points,
                        'rounds_won' => $participant->rounds_won,
                        'hazari_count' => $participant->hazari_count,
                        'position' => $participant->position
                    ];
                })
                ->sortByDesc('total_points')
                ->values()
                ->toArray();

            // Process payments
            $transactions = $this->processGamePayments($game, $winner);

            // Broadcast game over event to all players
            broadcast(new HajariGameOver($game, $winner, $finalScores));
            //broadcast(new GameWinner($game, $winner, $finalScores, $transactions));

            Log::info('Game Ended', [
                'game_id' => $game->id,
                'winner_id' => $winner->user_id,
                'winner_name' => $winner->user->name,
                'final_scores' => $finalScores,
                'transactions' => $transactions
            ]);
        });
    }

    private function calculateWinner(HajariGame $game)
    {
        return $game->participants()
            ->where('status', HajariGameParticipant::STATUS_PLAYING)
            ->orderByDesc('total_points')
            ->orderByDesc('rounds_won')
            ->orderByDesc('hazari_count')
            ->first();
    }

    private function processGamePayments(HajariGame $game, HajariGameParticipant $winner)
    {
        return DB::transaction(function () use ($game, $winner) {

            // Lock the game row to prevent race conditions in concurrent calls
            $game = HajariGame::where('id', $game->id)->lockForUpdate()->first();

            // Check again if payment is processed inside transaction
            if ($game->payment_processed) {
                Log::warning('Payments already processed for game: ' . $game->id);
                return [
                    'winner_amount' => 0,
                    'admin_commission' => 0
                ];
            }

            $bidAmount = $game->bid_amount;
            $participants = $game->participants()->get();

            $admin = User::find(1);
            $adminCommissionRate = GameSetting::getAdminCommission();

            $totalBidAmount = $bidAmount * 4;
            $adminCommission = $totalBidAmount * ($adminCommissionRate / 100);
            $winnerAmount = $totalBidAmount - $adminCommission;

            // Reduce admin credit safely
            $admin->decrement('credit', $winnerAmount);

            // Create transaction records safely inside transaction
            Transaction::create([
                'user_id' => $admin->id,
                'type' => 'debit',
                'amount' => $winnerAmount,
                'details' => 'Game Winning Amount for user: ' . $winner->user->name . ' for game: ' . $game->title,
            ]);

            Transaction::create([
                'user_id' => $winner->user_id,
                'type' => 'credit',
                'amount' => $winnerAmount,
                'details' => 'Game win: ' . $game->title . ' (After ' . $adminCommissionRate . '% admin commission)',
            ]);

            $winner->user->increment('credit', $winnerAmount);

            // Mark payment processed only after all DB operations succeed
            $game->update(['payment_processed' => true]);

            return [
                'winner_amount' => $winnerAmount,
                'admin_commission' => $adminCommission
            ];
        });
    }


    // private function processGamePayments(HajariGame $game, HajariGameParticipant $winner)
    // {
    //     // আগে চেক করুন
    //     if ($game->payment_processed) {
    //         Log::warning('Payments already processed for game: ' . $game->id);
    //         return [
    //             'winner_amount' => 0,
    //             'admin_commission' => 0
    //         ];
    //     }

    //     $bidAmount = $game->bid_amount;
    //     $participants = $game->participants()->get();

    //     return DB::transaction(function () use ($winner, $bidAmount, $participants, $game) {
    //         $admin = User::find(1);
    //         $adminCommissionRate = GameSetting::getAdminCommission();
    //         $totalBidAmount = $bidAmount * 4;
    //         $adminCommission = $totalBidAmount * ($adminCommissionRate / 100);
    //         $winnerAmount = $totalBidAmount - $adminCommission;

    //         // Add bid amount to admin account
    //         $admin->credit -= $winnerAmount;
    //         $admin->save();

    //         // Create transaction for admin (credit)
    //         Transaction::create([
    //             'user_id' => $admin->id,
    //             'type' => 'debit',
    //             'amount' => $winnerAmount,
    //             'details' => 'Game Winning Amount for user: ' . $winner->user->name . ' for game: ' . $game->title,
    //         ]);

    //         Transaction::create([
    //             'user_id' => $winner->user_id,
    //             'type' => 'credit',
    //             'amount' => $winnerAmount,
    //             'details' => 'Game win: ' . $game->title . ' (After ' . $adminCommissionRate . '% admin commission)',
    //         ]);

    //         $winner->user->increment('credit', $winnerAmount);

    //          // পেমেন্ট প্রসেসড হিসেবে চিহ্নিত করুন
    //         $game->update(['payment_processed' => true]);

    //         return [
    //             'winner_amount' => $winnerAmount,
    //             'admin_commission' => $adminCommission
    //         ];
    //     });
    // }
}

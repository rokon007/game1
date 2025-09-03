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
    public function checkGameEndConditions(HajariGame $game): bool
    {
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

        // সর্বোচ্চ পয়েন্ট প্রাপ্ত প্লেয়ারদের তালিকা করুন
        $potentialWinners = $game->participants()
            ->where('status', HajariGameParticipant::STATUS_PLAYING)
            ->where('total_points', $maxPoints)
            ->get();

        // যদি একাধিক প্লেয়ারের একই সর্বোচ্চ পয়েন্ট থাকে
        if ($potentialWinners->count() > 1) {
            // যে আগে সর্বোচ্চ পয়েন্ট অর্জন করেছে তাকে বিজয়ী করুন
            $winner = $this->determineEarliestMaxPointsWinner($potentialWinners, $game);
        } else {
            $winner = $potentialWinners->first();
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

            if ($earliestTime === null || $timeReachedMaxPoints < $earliestTime) {
                $earliestTime = $timeReachedMaxPoints;
                $earliestWinner = $winner;
            }
        }

        return $earliestWinner;
    }

    private function getTimeWhenReachedMaxPoints($participant, HajariGame $game)
    {
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
    }

    public function endGame(HajariGame $game, HajariGameParticipant $winner)
    {
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
            broadcast(new GameWinner($game, $winner, $finalScores, $transactions));

            Log::info('Game Ended', [
                'game_id' => $game->id,
                'winner_id' => $winner->user_id,
                'winner_name' => $winner->user->name,
                'final_scores' => $finalScores,
                'transactions' => $transactions
            ]);
        });
    }

    private function processGamePayments(HajariGame $game, HajariGameParticipant $winner)
    {
        $bidAmount = $game->bid_amount;
        $participants = $game->participants()->get();

        return DB::transaction(function () use ($winner, $bidAmount, $participants, $game) {
            $admin = User::find(1);
            $adminCommissionRate = GameSetting::getAdminCommission();
            $totalBidAmount = $bidAmount * 4;
            $adminCommission = $totalBidAmount * ($adminCommissionRate / 100);
            $winnerAmount = $totalBidAmount - $adminCommission;

            // Add bid amount to admin account
            $admin->credit -= $winnerAmount;
            $admin->save();

            // Create transaction for admin (credit)
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

            return [
                'winner_amount' => $winnerAmount,
                'admin_commission' => $adminCommission
            ];
        });
    }
}

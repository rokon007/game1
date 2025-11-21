<?php

namespace App\Services;

use App\Models\Lottery;
use App\Models\LotteryTicket;
use App\Models\LotteryResult;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class LotteryService
{
    // public function purchaseTicket(Lottery $lottery, User $user, int $quantity = 1): array
    // {
    //     if (!$lottery->isActive()) {
    //         throw new Exception('Lottery is no longer active.');
    //     }

    //     $totalCost = $lottery->price * $quantity;

    //     if (!$user->hasEnoughCredit($totalCost)) {
    //         throw new Exception('Insufficient credit.');
    //     }

    //     $tickets = [];

    //     DB::transaction(function () use ($lottery, $user, $quantity, $totalCost, &$tickets) {
    //         // Deduct credit from user
    //         $user->deductCredit($totalCost, "Lottery ticket purchase - {$lottery->name}");

    //         // Add credit to admin
    //         $admin = User::where('role', 'admin')->first();
    //         if ($admin) {
    //             $admin->addCredit($totalCost, "Lottery ticket sale - {$lottery->name}");
    //         }

    //         // Create tickets
    //         for ($i = 0; $i < $quantity; $i++) {
    //             $ticket = LotteryTicket::create([
    //                 'lottery_id' => $lottery->id,
    //                 'user_id' => $user->id,
    //                 'ticket_number' => LotteryTicket::generateUniqueTicketNumber(),
    //                 'purchased_at' => now()
    //             ]);

    //             $tickets[] = $ticket;
    //         }
    //     });

    //     return $tickets;
    // }

    public function purchaseTicket(Lottery $lottery, User $user, int $quantity = 1): array
    {
        if (!$lottery->isActive()) {
            throw new Exception('Lottery is no longer active.');
        }

        $totalCost = $lottery->price * $quantity;

        if ($user->total_balance < $totalCost) {
            throw new Exception('Insufficient balance.');
        }

        // if (auth()->user()->available_balance < $totalCost) {
        //     throw new Exception('Insufficient available balance.');
        // }

        $tickets = [];

        DB::transaction(function () use ($lottery, $user, $quantity, $totalCost, &$tickets) {
            // ✅ ইউজারের ব্যালেন্স থেকে কাটবে (bonus → credit)
            $user->spendBalance($totalCost, "Lottery ticket purchase - {$lottery->name}");

            // অ্যাডমিনকে ক্রেডিট যোগ করা
            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                $admin->addCredit($totalCost, "Lottery ticket sale - {$lottery->name}");
            }

            // 🎟️ টিকিট তৈরি করা
            for ($i = 0; $i < $quantity; $i++) {
                $ticket = LotteryTicket::create([
                    'lottery_id' => $lottery->id,
                    'user_id' => $user->id,
                    'ticket_number' => LotteryTicket::generateUniqueTicketNumber(),
                    'purchased_at' => now()
                ]);

                $tickets[] = $ticket;
            }
        });

        return $tickets;
    }


    public function conductDraw(Lottery $lottery): array
    {
        if ($lottery->status !== 'active') {
            throw new Exception('Lottery is not active.');
        }

        // Check if results already exist
        if ($lottery->results()->exists()) {
            throw new Exception('Draw has already been conducted for this lottery.');
        }

        $tickets = $lottery->tickets;
        if ($tickets->isEmpty()) {
            throw new Exception('No tickets sold.');
        }

        $results = [];
        $admin = User::where('role', 'admin')->first();

        DB::transaction(function () use ($lottery, $tickets, &$results, $admin) {
            // Start from lowest prize (highest rank) to highest prize (lowest rank)
            $prizes = $lottery->prizes()->orderBy('rank', 'desc')->get();
            $usedTickets = [];

            foreach ($prizes as $prize) {
                $winningTicket = null;

                // Check pre-selected winners
                if ($lottery->pre_selected_winners &&
                    isset($lottery->pre_selected_winners[$prize->position])) {

                    $preSelectedTicketNumber = $lottery->pre_selected_winners[$prize->position];
                    $winningTicket = $tickets->where('ticket_number', $preSelectedTicketNumber)->first();
                }

                // Random selection
                if (!$winningTicket) {
                    $availableTickets = $tickets->whereNotIn('id', $usedTickets);
                    if ($availableTickets->isNotEmpty()) {
                        $winningTicket = $availableTickets->random();
                    }
                }

                if ($winningTicket) {
                    $usedTickets[] = $winningTicket->id;

                    // Save result
                    $result = LotteryResult::create([
                        'lottery_id' => $lottery->id,
                        'lottery_prize_id' => $prize->id,
                        'lottery_ticket_id' => $winningTicket->id,
                        'user_id' => $winningTicket->user_id,
                        'winning_ticket_number' => $winningTicket->ticket_number,
                        'prize_amount' => $prize->amount,
                        'drawn_at' => now()
                    ]);

                    // Award prize
                    $winningTicket->user->addCredit(
                        $prize->amount,
                        "Lottery prize - {$prize->position} - {$lottery->name}"
                    );

                    // Deduct from admin
                    if ($admin) {
                        $admin->deductCredit(
                            $prize->amount,
                            "Lottery prize payment - {$prize->position} - {$lottery->name}"
                        );
                    }

                    $results[] = $result;
                }
            }

            // Mark lottery as completed
            $lottery->update(['status' => 'completed']);
        });

        return $results;
    }

    public function saveDrawResults(Lottery $lottery, array $drawResults): void
    {
        // Check if already completed to prevent duplicate saves
        if ($lottery->status === 'completed') {
            return;
        }

        // Check if results already exist
        if ($lottery->results()->exists()) {
            return;
        }

        $admin = User::where('role', 'admin')->first();

        DB::transaction(function () use ($lottery, $drawResults, $admin) {
            foreach ($drawResults as $resultData) {
                // Double check if this specific result already exists
                $existingResult = LotteryResult::where('lottery_id', $lottery->id)
                    ->where('lottery_prize_id', $resultData['lottery_prize_id'])
                    ->first();

                if ($existingResult) {
                    continue; // Skip if already exists
                }

                $winningTicket = LotteryTicket::find($resultData['lottery_ticket_id']);
                $prize = $lottery->prizes()->where('position', $resultData['prize_position'])->first();

                if ($winningTicket && $prize) {
                    // Save result
                    LotteryResult::create([
                        'lottery_id' => $lottery->id,
                        'lottery_prize_id' => $prize->id,
                        'lottery_ticket_id' => $winningTicket->id,
                        'user_id' => $winningTicket->user_id,
                        'winning_ticket_number' => $resultData['winning_ticket_number'],
                        'prize_amount' => $resultData['prize_amount'],
                        'drawn_at' => now()
                    ]);

                    // Add credit to winner
                    $winningTicket->user->addCredit(
                        $resultData['prize_amount'],
                        "Lottery prize - {$resultData['prize_position']} - {$lottery->name}"
                    );

                    // Deduct credit from admin
                    if ($admin) {
                        $admin->deductCredit(
                            $resultData['prize_amount'],
                            "Lottery prize payment - {$resultData['prize_position']} - {$lottery->name}"
                        );
                    }
                }
            }

            // Mark lottery as completed
            $lottery->update(['status' => 'completed']);
        });
    }
}

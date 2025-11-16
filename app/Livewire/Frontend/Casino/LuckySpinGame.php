<?php

namespace App\Livewire\Frontend\Casino;

use Livewire\Component;
use App\Models\LuckySpin;
use App\Models\SystemPool;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;

class LuckySpinGame extends Component
{
    public $betAmount = 100;
    public $spinning = false;
    public $result = null;
    public $credit = 0;
    public $poolAmount = 0;
    public $reward = 0;
    public $winAmount = 0;
    public $spinAngle = 0;

    // Store pre-calculated result
    public $preCalculatedResult = null;
    public $preCalculatedMultiplier = null;
    public $preCalculatedReward = null;

    public $minAmaunt, $maxAmaunt;

    public function mount()
    {
        $this->minAmaunt = SystemSetting::getValue('min_bet', 10);
        $this->maxAmaunt = SystemSetting::getValue('max_bet', 10000);
        $this->betAmount = $this->minAmaunt;

        if (!SystemPool::first()) {
            SystemPool::create(['total_collected' => 0]);
        }

        if (auth()->check()) {
            $this->credit = auth()->user()->credit;
        }

        $this->updatePoolAmount();
    }

    protected function rules()
    {
        return [
            'betAmount' => 'required|integer|min:' . $this->minAmaunt,
        ];
    }

    public function incrementBet()
    {
        $this->betAmount += $this->minAmaunt;
        $this->validate(['betAmount' => 'required|integer|min:'. $this->minAmaunt,]);
    }

    public function decrementBet()
    {
        $this->betAmount = max($this->minAmaunt, $this->betAmount - $this->minAmaunt);
        $this->validate(['betAmount' => 'required|integer|min:'. $this->minAmaunt,]);
    }

    /**
     * 🎲 FIXED: Preview with Random Jackpot Chance
     */
    // public function previewResult()
    // {
    //     $user = auth()->user();
    //     if (!$user || $user->credit < $this->betAmount) {
    //         return ['result' => 'lose', 'multiplier' => 0, 'reward' => 0];
    //     }

    //     // Get settings
    //     $jackpotLimit = (int) SystemSetting::getValue('jackpot_limit', 100000);
    //     $jackpotChance = (float) SystemSetting::getValue('jackpot_chance_percent', 0.1); // 0.1%
    //     $winChancePercent = (int) SystemSetting::getValue('win_chance_percent', 20);
    //     $minimumPoolReserve = (int) SystemSetting::getValue('minimum_pool_reserve', 10000);

    //     // Lock pool for accurate reading
    //     $pool = SystemPool::lockForUpdate()->first();

    //     // Calculate available pool (excluding reserve)
    //     $availablePool = max(0, $pool->total_collected - $minimumPoolReserve);

    //     // Reset pre-calculated values
    //     $this->preCalculatedResult = null;
    //     $this->preCalculatedMultiplier = null;
    //     $this->preCalculatedReward = null;

    //     // 🎰 STEP 1: Check Jackpot (RANDOM CHANCE)
    //     if ($pool->total_collected >= $jackpotLimit) {
    //         $randomJackpot = mt_rand(1, 10000) / 100; // 0.01 to 100.00

    //         if ($randomJackpot <= $jackpotChance) {
    //             // JACKPOT WON!
    //             $adminCommissionPercent = (int) SystemSetting::getValue('admin_commission', 10);
    //             $adminCommission = (int) floor($pool->total_collected * ($adminCommissionPercent / 100));
    //             $reward = $pool->total_collected - $adminCommission;
    //             $multiplier = $reward > 0 ? round($reward / $this->betAmount, 2) : 0;

    //             $this->preCalculatedResult = 'jackpot';
    //             $this->preCalculatedMultiplier = $multiplier;
    //             $this->preCalculatedReward = $reward;

    //             return [
    //                 'result' => 'jackpot',
    //                 'multiplier' => $multiplier,
    //                 'reward' => $reward
    //             ];
    //         }
    //     }

    //     // 🎲 STEP 2: Check Regular Win
    //     $randomWin = rand(1, 100);

    //     if ($randomWin <= $winChancePercent) {
    //         // Calculate max possible multiplier based on available pool
    //         $maxPossibleMultiplier = $availablePool > 0
    //             ? floor($availablePool / $this->betAmount)
    //             : 0;

    //         $possibleMultipliers = [2, 3, 4, 5];
    //         $validMultipliers = array_filter($possibleMultipliers, function($m) use ($maxPossibleMultiplier) {
    //             return $m <= $maxPossibleMultiplier;
    //         });

    //         if (count($validMultipliers) > 0) {
    //             $validMultipliersArray = array_values($validMultipliers);
    //             $randomIndex = rand(0, count($validMultipliersArray) - 1);
    //             $multiplier = $validMultipliersArray[$randomIndex];
    //             $reward = (int) floor($this->betAmount * $multiplier);

    //             // Apply max win limit (50% of available pool)
    //             $maxWinLimit = (int) floor($availablePool * 0.5);
    //             if ($reward > $maxWinLimit) {
    //                 $reward = $maxWinLimit;
    //                 $multiplier = $reward > 0 ? round($reward / $this->betAmount, 2) : 0;
    //             }

    //             $this->preCalculatedResult = 'win';
    //             $this->preCalculatedMultiplier = $multiplier;
    //             $this->preCalculatedReward = $reward;

    //             return [
    //                 'result' => 'win',
    //                 'multiplier' => $multiplier,
    //                 'reward' => $reward
    //             ];
    //         }
    //     }

    //     // 🎲 STEP 3: Lose (show realistic multiplier = 0)
    //     $this->preCalculatedResult = 'lose';
    //     $this->preCalculatedMultiplier = 0;
    //     $this->preCalculatedReward = 0;

    //     return [
    //         'result' => 'lose',
    //         'multiplier' => 0, // Honest display
    //         'reward' => 0
    //     ];
    // }

    // Pre-calculate what the result will be (called from frontend)
    public function previewResult()
    {
        $user = auth()->user();
        if (!$user || $user->credit < $this->betAmount) {
            return ['result' => 'lose', 'multiplier' => 0, 'reward' => 0];
        }

        $pool = SystemPool::first();
        $jackpotLimit = (int) SystemSetting::getValue('jackpot_limit', 100000);
        $winChancePercent = (int) SystemSetting::getValue('win_chance_percent', 20);
        $adminCommissionPercent = (int) SystemSetting::getValue('admin_commission', 10);

        // Determine result with a fresh random seed
        $randomSeed = rand(1, 100);

        // Store the seed and timestamp to use in actual spin
        $this->preCalculatedResult = null;
        $this->preCalculatedMultiplier = null;
        $this->preCalculatedReward = null;

        if ($pool->total_collected >= $jackpotLimit) {
            $adminCommission = (int) floor($pool->total_collected * ($adminCommissionPercent / 100));
            $reward = $pool->total_collected - $adminCommission;
            $multiplier = $reward > 0 ? round($reward / $this->betAmount, 2) : 0;

            // Store pre-calculated values
            $this->preCalculatedResult = 'jackpot';
            $this->preCalculatedMultiplier = $multiplier;
            $this->preCalculatedReward = $reward;

            return [
                'result' => 'jackpot',
                'multiplier' => $multiplier,
                'reward' => $reward
            ];
        }

        if ($randomSeed <= $winChancePercent) {
            // Will win - calculate exact multiplier now
            $maxPossibleMultiplier = $pool->total_collected > 0
                ? floor($pool->total_collected / $this->betAmount)
                : 0;

            $possibleMultipliers = [2, 3, 4, 5];
            $validMultipliers = array_filter($possibleMultipliers, function($m) use ($maxPossibleMultiplier) {
                return $m <= $maxPossibleMultiplier;
            });

            if (count($validMultipliers) > 0) {
                // FIX: Properly get random multiplier from valid multipliers
                $validMultipliersArray = array_values($validMultipliers);
                $randomIndex = rand(0, count($validMultipliersArray) - 1);
                $multiplier = $validMultipliersArray[$randomIndex];
                $reward = (int) floor($this->betAmount * $multiplier);

                if ($reward > $pool->total_collected) {
                    $reward = $pool->total_collected;
                    $multiplier = $reward > 0 ? round($reward / $this->betAmount, 2) : 0;
                }

                // Store pre-calculated values
                $this->preCalculatedResult = 'win';
                $this->preCalculatedMultiplier = $multiplier;
                $this->preCalculatedReward = $reward;

                return [
                    'result' => 'win',
                    'multiplier' => $multiplier,
                    'reward' => $reward
                ];
            }
        }

        // Will lose - show random exciting multiplier (fake)
        $excitingMultipliers = [2, 3, 5, 10, 15, 20, 50, 100];
        $fakeMultiplier = $excitingMultipliers[array_rand($excitingMultipliers)];

        // Store that this is a lose
        $this->preCalculatedResult = 'lose';
        $this->preCalculatedMultiplier = 0;
        $this->preCalculatedReward = 0;

        return [
            'result' => 'lose',
            'multiplier' => $fakeMultiplier, // Fake for display only
            'reward' => 0
        ];
    }

    /**
     * 🎰 MAIN SPIN FUNCTION - FIXED
     */
    public function spin()
    {
        $this->validate();

        $user = auth()->user();
        if (!$user) {
            session()->flash('error', 'Please login first.');
            return;
        }

        $min = (int) SystemSetting::getValue('min_bet', 10);
        $max = (int) SystemSetting::getValue('max_bet', 10000);

        if ($this->betAmount < $min || $this->betAmount > $max) {
            session()->flash('error', "Bet must be between {$min} and {$max}");
            return;
        }

        if ($user->credit < $this->betAmount) {
            session()->flash('error', 'Insufficient balance.');
            return;
        }

        $this->spinning = true;
        $this->result = null;
        $this->reward = 0;

        \DB::beginTransaction();
        try {
            // 🔒 LOCK: Pool and User
            $pool = SystemPool::lockForUpdate()->first();
            $user = User::lockForUpdate()->find($user->id);

            // Recheck credit after lock
            if ($user->credit < $this->betAmount) {
                \DB::rollBack();
                session()->flash('error', 'Insufficient balance.');
                $this->spinning = false;
                return;
            }

            // Get settings
            $jackpotLimit = (int) SystemSetting::getValue('jackpot_limit', 100000);
            $jackpotChance = (float) SystemSetting::getValue('jackpot_chance_percent', 0.1);
            $adminCommissionPercent = (int) SystemSetting::getValue('admin_commission', 10);
            $minimumPoolReserve = (int) SystemSetting::getValue('minimum_pool_reserve', 10000);

            $poolBefore = $pool->total_collected;
            $initialCredit = $user->credit;

            // 💰 DEDUCT BET FROM USER (always happens first)
            $user->decrement('credit', $this->betAmount);
            $this->credit = $user->credit;

            // 💰 ADD BET TO POOL (not to admin)
            $pool->total_collected += $this->betAmount;

            // Calculate available pool
            $availablePool = max(0, $pool->total_collected - $minimumPoolReserve);

            $result = $this->preCalculatedResult ?? 'lose';
            $multiplier = $this->preCalculatedMultiplier ?? 0;
            $reward = $this->preCalculatedReward ?? 0;
            $adminCommission = 0;

            // 🎰 DETERMINE OUTCOME

            // 1️⃣ CHECK JACKPOT (with random chance)
            if ($result === 'jackpot' && $pool->total_collected >= $jackpotLimit) {
                // Re-verify jackpot eligibility
                $randomJackpot = mt_rand(1, 10000) / 100;

                if ($randomJackpot <= $jackpotChance) {
                    // ✅ JACKPOT CONFIRMED
                    $adminCommission = (int) floor($pool->total_collected * ($adminCommissionPercent / 100));
                    $reward = $pool->total_collected - $adminCommission;
                    $multiplier = $reward > 0 ? round($reward / $this->betAmount, 2) : 0;

                    \Log::info("🎉 Jackpot won by user {$user->id}! Pool: {$pool->total_collected}, Commission: {$adminCommission}, Reward: {$reward}");

                    // Reset pool after jackpot
                    $pool->total_collected = 0;
                    $pool->last_jackpot_at = now();
                } else {
                    // ❌ Jackpot chance missed, convert to regular spin
                    \Log::info("❌ Jackpot chance missed for user {$user->id}");
                    $result = 'lose';
                    $multiplier = 0;
                    $reward = 0;
                }
            }
            // 2️⃣ REGULAR WIN
            elseif ($result === 'win') {
                // Verify pool has enough
                if ($reward > $availablePool) {
                    $reward = $availablePool;
                    $multiplier = $reward > 0 ? round($reward / $this->betAmount, 2) : 0;
                }

                if ($reward > 0) {
                    // Calculate admin commission on win
                    $adminCommission = (int) floor($reward * ($adminCommissionPercent / 100));
                    $netReward = $reward - $adminCommission;

                    // Deduct from pool
                    $pool->total_collected -= $reward;

                    // Give net reward to user
                    $reward = $netReward;
                } else {
                    // Pool exhausted, convert to lose
                    $result = 'lose';
                    $multiplier = 0;
                    $reward = 0;
                }
            }
            // 3️⃣ LOSE
            else {
                $result = 'lose';
                $reward = 0;
                $multiplier = 0;
                // Bet already added to pool above
            }

            // 💰 APPLY REWARDS
            if ($reward > 0) {
                $user->increment('credit', $reward);

                // User credit transaction
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'credit',
                    'amount' => $reward,
                    'details' => "Lucky Spin Win - {$result} ({$multiplier}x)"
                ]);
            }

            // 💰 GIVE COMMISSION TO ADMIN (only if there's commission)
            if ($adminCommission > 0) {
                $adminUser = User::find(1); // Admin ID = 1
                if ($adminUser) {
                    $adminUser->increment('credit', $adminCommission);

                    Transaction::create([
                        'user_id' => $adminUser->id,
                        'type' => 'credit',
                        'amount' => $adminCommission,
                        'details' => "Lucky Spin Commission from User #{$user->id} - {$result}"
                    ]);
                }
            }

            $poolAfter = $pool->total_collected;
            $pool->save();

            // Create spin record
            LuckySpin::create([
                'user_id' => $user->id,
                'bet_amount' => $this->betAmount,
                'result' => $result,
                'reward_amount' => $reward,
                'system_pool_before' => $poolBefore,
                'system_pool_after' => $poolAfter,
            ]);

            \DB::commit();

            // Clear pre-calculated values
            $this->preCalculatedResult = null;
            $this->preCalculatedMultiplier = null;
            $this->preCalculatedReward = null;

            $finalCredit = $user->fresh()->credit;

            // Update pool amount
            $this->updatePoolAmount();
            $this->result = $result;
            $this->reward = $reward;

            // Calculate angle for result
            $angle = $this->calculateAngleForResult($result);
            $this->spinAngle = $angle;

            // Dispatch browser event
            $this->dispatch('spin-wheel', [
                'angle' => $angle,
                'result' => $result,
                'reward' => $reward,
                'multiplier' => $multiplier,
                'bet_amount' => $this->betAmount,
                'pool_before' => $poolBefore,
                'pool_after' => $poolAfter,
                'admin_commission' => $adminCommission,
                'final_credit' => $finalCredit,
                'initial_credit' => $this->credit
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Spin Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            session()->flash('error', 'Server error. Try again.');
        }

        $this->spinning = false;
    }

    private function calculateAngleForResult($result)
    {
        $segments = [
            'lose' => [0, 120, 240],
            'win' => [60, 300],
            'jackpot' => [180]
        ];

        $availableAngles = $segments[$result];
        $baseAngle = $availableAngles[array_rand($availableAngles)];

        $offset = rand(10, 50);
        return $baseAngle + $offset;
    }

    private function updatePoolAmount()
    {
        $pool = SystemPool::first();
        $this->poolAmount = $pool ? $pool->total_collected : 0;
    }

    public function render()
    {
        return view('livewire.frontend.casino.lucky-spin-game')->layout('livewire.layout.frontend.base');
    }
}

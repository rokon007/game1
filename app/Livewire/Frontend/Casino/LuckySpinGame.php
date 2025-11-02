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

    // protected $rules = [
    //     'betAmount' => 'required|integer|min:1'
    // ];

    public $minAmaunt, $maxAmaunt;

    public function mount()
    {
        $this->minAmaunt = SystemSetting::getValue('min_bet', 10);
        $this->maxAmaunt = SystemSetting::getValue('max_bet', 10000);
        $this->betAmount= $this->minAmaunt;

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
            // Lock the pool and user for update
            $pool = SystemPool::lockForUpdate()->first();
            $user = User::lockForUpdate()->find($user->id);

            $poolBefore = $pool->total_collected;

            // Check credit again after lock (to prevent race condition)
            if ($user->credit < $this->betAmount) {
                \DB::rollBack();
                session()->flash('error', 'Insufficient balance.');
                $this->spinning = false;
                return;
            }

            // Store initial credit before deduction
            $initialCredit = $user->credit;

            // Debit user and create transaction
            $user->decrement('credit', $this->betAmount);

            // Update component credit to show deduction immediately
            $this->credit = $user->credit;

            // Create debit transaction for user
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $this->betAmount,
                'details' => 'Placed bet for lucky spin'
            ]);

            // Credit admin (house) - assuming admin user ID is 1
            $adminUser = User::find(1);
            if ($adminUser) {
                $adminUser->increment('credit', $this->betAmount);
                Transaction::create([
                    'user_id' => $adminUser->id,
                    'type' => 'credit',
                    'amount' => $this->betAmount,
                    'details' => 'Received bet from user for lucky spin'
                ]);
            }

            $adminCommissionPercent = (int) SystemSetting::getValue('admin_commission', 10);
            $jackpotLimit = (int) SystemSetting::getValue('jackpot_limit', 100000);

            // Use pre-calculated results if available
            $result = $this->preCalculatedResult ?? 'lose';
            $multiplier = $this->preCalculatedMultiplier ?? 0;
            $reward = $this->preCalculatedReward ?? 0;
            $adminCommission = 0;

            // CRITICAL: Check if jackpot is still available (only one user can win)
            if ($result === 'jackpot') {
                // Re-check if pool still qualifies for jackpot after lock
                if ($pool->total_collected >= $jackpotLimit) {
                    // Calculate admin commission
                    $adminCommission = (int) floor($pool->total_collected * ($adminCommissionPercent / 100));

                    // Recalculate reward based on current pool
                    $reward = $pool->total_collected - $adminCommission;
                    $multiplier = $reward > 0 ? round($reward / $this->betAmount, 2) : 0;

                    \Log::info("Jackpot won by user {$user->id}! Pool: {$pool->total_collected}, Commission: {$adminCommission}, Reward: {$reward}");

                    // Reset pool to zero after jackpot (this prevents others from winning)
                    $pool->total_collected = 0;
                    $pool->last_jackpot_at = now();
                } else {
                    // Pool was already won by someone else, convert to regular win or lose
                    \Log::info("Jackpot already claimed! Converting to regular spin for user {$user->id}");

                    // Give them a consolation win if pool has enough
                    if ($pool->total_collected >= ($this->betAmount * 2)) {
                        $result = 'win';
                        $multiplier = 2;
                        $reward = $this->betAmount * 2;
                        $pool->total_collected -= $reward;
                    } else {
                        // Convert to lose
                        $result = 'lose';
                        $multiplier = 0;
                        $reward = 0;
                        $pool->total_collected += $this->betAmount;
                    }
                }

            } else if ($result === 'win') {
                // Use pre-calculated reward and multiplier
                // Double check we don't exceed current pool
                if ($reward > $pool->total_collected) {
                    $reward = $pool->total_collected;
                    $multiplier = $reward > 0 ? round($reward / $this->betAmount, 2) : 0;
                }

                // Deduct reward from pool
                $pool->total_collected -= $reward;

            } else {
                // Lose - add bet to pool
                $pool->total_collected += $this->betAmount;
                $reward = 0;
                $multiplier = 0;
            }

            // Apply reward and create transactions
            if ($reward > 0) {
                $user->increment('credit', $reward);

                // Create credit transaction for user
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'credit',
                    'amount' => $reward,
                    'details' => 'Won reward from lucky spin - ' . $result . ' (' . $multiplier . 'x)'
                ]);

                // Debit admin (house) for the reward
                if ($adminUser) {
                    $adminUser->decrement('credit', $reward);
                    Transaction::create([
                        'user_id' => $adminUser->id,
                        'type' => 'debit',
                        'amount' => $reward,
                        'details' => 'Paid reward to user for lucky spin - ' . $result . ' (' . $multiplier . 'x)'
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

            // Get the final credit after reward (but don't update component state yet)
            $finalCredit = $user->fresh()->credit;

            // Update pool amount
            $this->updatePoolAmount();
            $this->result = $result;
            $this->reward = $reward;

            // Calculate angle for the result
            $angle = $this->calculateAngleForResult($result);
            $this->spinAngle = $angle;

            // Dispatch browser event for wheel animation with actual multiplier
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
            session()->flash('error', 'Server error. Try again.');
        }

        $this->spinning = false;
    }

    private function calculateAngleForResult($result)
    {
        // Wheel has 6 segments of 60 degrees each
        $segments = [
            'lose' => [0, 120, 240],     // 3 lose segments
            'win' => [60, 300],          // 2 win segments
            'jackpot' => [180]           // 1 jackpot segment
        ];

        $availableAngles = $segments[$result];
        $baseAngle = $availableAngles[array_rand($availableAngles)];

        // Add random offset within the segment (avoid edges)
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

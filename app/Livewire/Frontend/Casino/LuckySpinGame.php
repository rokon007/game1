<?php

namespace App\Livewire\Frontend\Casino;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\LuckySpin;
use App\Models\SystemPool;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;

class LuckySpinGame extends Component
{
    public $betAmount = 05;
    public $spinning = false;
    public $result = null;
    public $credit = 0;
    public $poolAmount = 0;
    public $reward = 0;
    public $winAmount = 0;
    public $spinAngle = 0;

    protected $rules = [
        'betAmount' => 'required|integer|min:1'
    ];

    public function mount()
    {
        if (!SystemPool::first()) {
            SystemPool::create(['total_collected' => 0]);
        }

        if (auth()->check()) {
            $this->credit = auth()->user()->credit;
        }

        $this->updatePoolAmount();
    }

    public function incrementBet()
    {
        $this->betAmount += 5;
        $this->validate(['betAmount' => 'required|integer|min:1']);
    }

    public function decrementBet()
    {
        $this->betAmount = max(100, $this->betAmount - 5);
        $this->validate(['betAmount' => 'required|integer|min:1']);
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
            $pool = SystemPool::lockForUpdate()->first();
            $poolBefore = $pool->total_collected;

            // Debit user and create transaction
            $user->decrement('credit', $this->betAmount);

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

            $jackpotLimit = (int) SystemSetting::getValue('jackpot_limit', 100000);
            $winChancePercent = (int) SystemSetting::getValue('win_chance_percent', 20);

            $result = 'lose';
            $reward = 0;

            // Jackpot check
            if ($pool->total_collected >= $jackpotLimit) {
                $result = 'jackpot';
                $reward = $pool->total_collected;
                $pool->total_collected = 0;
                $pool->last_jackpot_at = now();
            } else {
                $rand = rand(1, 100);
                if ($rand <= $winChancePercent) {
                    $result = 'win';
                    $multiplier = rand(2, 5);
                    $reward = (int) floor($this->betAmount * $multiplier);

                    if ($pool->total_collected >= $reward) {
                        $pool->total_collected -= $reward;
                    } else {
                        $pool->total_collected = 0;
                    }
                } else {
                    $pool->total_collected += $this->betAmount;
                }
            }

            // Apply reward and create transactions
            if ($reward > 0) {
                $user->increment('credit', $reward);

                // Create credit transaction for user
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'credit',
                    'amount' => $reward,
                    'details' => 'Won reward from lucky spin - ' . $result
                ]);

                // Debit admin (house) for the reward
                if ($adminUser) {
                    $adminUser->decrement('credit', $reward);
                    Transaction::create([
                        'user_id' => $adminUser->id,
                        'type' => 'debit',
                        'amount' => $reward,
                        'details' => 'Paid reward to user for lucky spin - ' . $result
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

            // Update component state
            $this->credit = $user->fresh()->credit;
            $this->updatePoolAmount();
            $this->result = $result;
            $this->reward = $reward;

            // Calculate angle for the result
            $angle = $this->calculateAngleForResult($result);
            $this->spinAngle = $angle;

            // Dispatch browser event for wheel animation
            $this->dispatch('spin-wheel', [
                'angle' => $angle,
                'result' => $result,
                'reward' => $reward,
                'pool_before' => $poolBefore,
                'pool_after' => $poolAfter
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

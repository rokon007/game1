<?php

namespace App\Livewire\Backend;

use Livewire\Component;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CreditTransferred;
use App\Models\SystemPool;
use App\Models\SystemSetting;
use App\Events\WinnerAnnouncedEvent;

class Dashboard extends Component
{
    public $user_credit;
    public $rechargeModal=false;
    public $amountMode=false;
    public $confirmMode=false;
    public $amount, $password;
    public $transactionSuccess=false;
    public $rechargeUser_id;
    public $totalUsers;
    public $poolAmount = 0;
    public $jackpotLimit=0;
    public $progressPercent;

    public function mount()
    {
        $this->totalUsers = User::count();
        $this->getCredit();
        $this->rechargeUser_id=auth()->user()->id;
        $this->updatePoolAmount();
    }

    public function updatePoolAmount()
    {
        $pool = SystemPool::first();
        $this->poolAmount = $pool ? $pool->total_collected : 0;
        $this->jackpotLimit = SystemSetting::getValue('jackpot_limit', 100000);

        // progress percentage হিসাব
        $this->progressPercent = $this->jackpotLimit > 0
            ? min(100, ($this->poolAmount / $this->jackpotLimit) * 100)
            : 0;
    }

    public function testClick()
    {
        event(new \App\Events\GameOverEvent(23));
    }

    public function addMony()
    {
        $this->rechargeModal=true;
        $this->amountMode=true;
    }

    public function rechargeNext()
    {
        $this->validate([
            'amount' => ['required']
        ]);
        $this->amountMode=false;
        $this->confirmMode=true;
    }

    public function closeRechargeModal()
    {
        $this->reset(['amount', 'password']);
        $this->amountMode=false;
        $this->confirmMode=false;
        $this->amountMode=false;
        $this->rechargeModal=false;
    }

    public function comfirm($id)
    {

        $this->validate([
            'password' => ['required']
        ]);

        $authUser = auth()->user();

        if (!Hash::check($this->password, $authUser->password)) {
            $this->addError('password', 'Invalid password.');
            return;
        }

        // Perform transaction
        $receiver = User::find($id);
        $amount = $this->amount;

        // Start DB transaction if needed for safety
        \DB::transaction(function () use ($authUser, $receiver, $amount) {
            // Update balances
            //$authUser->decrement('credit', $amount);
            $authUser->increment('credit', $amount);

            // Sender transaction
            Transaction::create([
                'user_id' => $authUser->id,
                'type' => 'debit',
                'amount' => $amount,
                'details' => 'Credit received',
            ]);

            // Receiver transaction
            // Transaction::create([
            //     'user_id' => $receiver->id,
            //     'type' => 'credit',
            //     'amount' => $amount,
            //     'details' => 'Credit received from ' . $authUser->name,
            // ]);

            // Notify both users
            //Notification::send($authUser, new CreditTransferred('You sent ' . $amount . ' credits to ' . $receiver->name));
            Notification::send($authUser, new CreditTransferred('You received ' . $amount . ' credits from ' . $authUser->name));
            $this->getCredit();
        });

        $this->closeRechargeModal();
        $this->transactionSuccess = true;
    }

    public function getCredit()
    {
        $this->user_credit=auth()->user()->credit;
    }

    public function render()
    {
        return view('livewire.backend.dashboard')->layout('livewire.backend.base');
    }
}

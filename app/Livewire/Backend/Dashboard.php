<?php

namespace App\Livewire\Backend;

use Livewire\Component;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CreditTransferred;

class Dashboard extends Component
{
    public $user_credit;
    public $rechargeModal=false;
    public $amountMode=false;
    public $confirmMode=false;
    public $amount, $password;
    public $transactionSuccess=false;
    public $rechargeUser_id;

    public function mount()
    {
        $this->getCredit();
        $this->rechargeUser_id=auth()->user()->id;
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

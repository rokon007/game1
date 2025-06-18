<?php

namespace App\Livewire\Frontend;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CreditTransferred;
use Livewire\Component;

class CreditTransferForm extends Component
{
    public $sendingForm=true;
    public $confermationForm=false;
    public $successNotification=false;

    public $mobile, $amount, $password, $receiverData, $unique_id;

    // public function sendingNext()
    // {
    //     $this->validate([
    //         'mobile' => ['required', 'string'],
    //         'amount' => ['required', 'numeric', 'min:1'],
    //     ]);

    //     $authUser = auth()->user();

    //     // নিজের মোবাইল নম্বর চেক
    //     if ($this->mobile === $authUser->mobile) {
    //         $this->addError('mobile', 'You cannot send credit to your own account.');
    //         return;
    //     }

    //     // রিসিভার ইউজার চেক
    //     $receiver = \App\Models\User::where('mobile', $this->mobile)->first();
    //     $this->receiverData=$receiver;
    //     if (!$receiver) {
    //         $this->addError('mobile', 'The mobile number is not associated with any user.');
    //         return;
    //     }

    //     // ব্যালেন্স চেক
    //     if ($this->amount > $authUser->credit) {
    //         $this->addError('amount', 'Insufficient balance for this transfer.');
    //         return;
    //     }

    //     // সব ঠিক থাকলে পরবর্তী ধাপে যাওয়া
    //     $this->sendingForm = false;
    //     $this->confermationForm = true;
    // }

    public function sendingNext()
    {
        $this->validate([
            'unique_id' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $authUser = auth()->user();

        if ($this->unique_id === $authUser->unique_id) {
            $this->addError('unique_id', 'You cannot send credit to your own account.');
            return;
        }

        $receiver = User::where('unique_id', $this->unique_id)->first();
        if (!$receiver) {
            $this->addError('unique_id', 'The ID is not associated with any user.');
            return;
        }

        if ($this->amount > $authUser->credit) {
            $this->addError('amount', 'Insufficient balance for this transfer.');
            return;
        }

        $this->receiverData = $receiver;
        $this->sendingForm = false;
        $this->confermationForm = true;
    }

    public function confirmationAction()
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
        $receiver = $this->receiverData;
        $amount = $this->amount;

        // Start DB transaction if needed for safety
        \DB::transaction(function () use ($authUser, $receiver, $amount) {
            // Update balances
            $authUser->decrement('credit', $amount);
            $receiver->increment('credit', $amount);

            // Sender transaction
            Transaction::create([
                'user_id' => $authUser->id,
                'type' => 'debit',
                'amount' => $amount,
                'details' => 'Credit sent to ' . $receiver->unique_id,
            ]);

            // Receiver transaction
            Transaction::create([
                'user_id' => $receiver->id,
                'type' => 'credit',
                'amount' => $amount,
                'details' => 'Credit received from ' . $authUser->unique_id,
            ]);

            // Notify both users
            Notification::send($authUser, new CreditTransferred('You sent ' . $amount . ' credits to ' . $receiver->unique_id));
            Notification::send($receiver, new CreditTransferred('You received ' . $amount . ' credits from ' . $authUser->unique_id));
        });

        $this->reset(['mobile', 'amount', 'password', 'receiverData']);
        $this->sendingForm = false;
        $this->confermationForm = false;
        $this->successNotification = true;
    }



    public function render()
    {
        return view('livewire.frontend.credit-transfer-form')->layout('livewire.layout.frontend.base');
    }
}

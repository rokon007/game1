<?php

namespace App\Livewire\Frontend;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CreditTransferred;
use Livewire\Component;
use App\Models\Referral;
use App\Models\ReferralSetting;
use App\Notifications\ReferralCommissionEarned;
use Illuminate\Support\Facades\DB;


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

        // Check available balance
        if ($authUser->available_balance < $this->amount) {
            session()->flash('error', 'Insufficient available balance. Your current usable balance is ');
            return;
        }

        $this->receiverData = $receiver;
        $this->sendingForm = false;
        $this->confermationForm = true;
    }

    /**
     * ক্রেডিট ট্রান্সফার নিশ্চিত করা এবং রেফারেল কমিশন হ্যান্ডল করা।
     */
    public function confirmationAction()
    {
        $this->validate([
            'password' => ['required']
        ]);

        $authUser = auth()->user();

        // পাসওয়ার্ড সঠিক কিনা তা চেক করা
        if (!Hash::check($this->password, $authUser->password)) {
            $this->addError('password', 'Invalid password.');
            return;
        }

        // ট্রানজাকশন সম্পাদন করা
        $receiver = $this->receiverData;
        $amount = $this->amount;

        // নিরাপত্তার জন্য ডাটাবেস ট্রানজাকশন শুরু করা
        \DB::transaction(function () use ($authUser, $receiver, $amount) {
            // ব্যালেন্স আপডেট করা
            $authUser->decrement('credit', $amount);
            $receiver->increment('credit', $amount);

            // প্রেরকের ট্রানজাকশন তৈরি করা
            Transaction::create([
                'user_id' => $authUser->id,
                'type' => 'debit',
                'amount' => $amount,
                'details' => 'Credit sent to ' . $receiver->unique_id,
            ]);

            // প্রাপকের ট্রানজাকশন তৈরি করা
            Transaction::create([
                'user_id' => $receiver->id,
                'type' => 'credit',
                'amount' => $amount,
                'details' => 'Credit received from ' . $authUser->unique_id,
            ]);

            // উভয় ইউজারকে নোটিফিকেশন পাঠানো
            Notification::send($authUser, new CreditTransferred('You sent ' . $amount . ' credits to ' . $receiver->unique_id));
            Notification::send($receiver, new CreditTransferred('You received ' . $amount . ' credits from ' . $authUser->unique_id));

            // রেফারেল কমিশন হ্যান্ডল করা
            // $referral = Referral::where('referred_user_id', $receiver->id)->first();
            // if ($referral) {
            //     $settings = ReferralSetting::first();
            //     if ($settings && $referral->commission_count < $settings->max_commission_count) {
            //         $commission = ($amount * $settings->commission_percentage) / 100;

            //         // রেফারারের ক্রেডিট আপডেট করা
            //         $referrer = User::find($referral->referrer_id);
            //         $referrer->credit += $commission;
            //         $referrer->save();

            //         // রেফারারের জন্য ট্রানজাকশন তৈরি করা
            //         Transaction::create([
            //             'user_id' => $referrer->id,
            //             'type' => 'credit',
            //             'amount' => $commission,
            //             'details' => 'Referral commission for user ' . $receiver->unique_id,
            //         ]);

            //         // অ্যাডমিনের জন্য ট্রানজাকশন (ডেবিট)
            //         $admin = User::where('role', 'admin')->first();
            //         if ($admin) {
            //             // অ্যাডমিনের ক্রেডিট পর্যাপ্ত কিনা চেক করা
            //             if ($admin->credit < $commission) {
            //                 throw new \Exception('The admin does not have sufficient credit for the referral commission.');
            //             }

            //             $admin->credit -= $commission;
            //             $admin->save();

            //             Transaction::create([
            //                 'user_id' => $admin->id,
            //                 'type' => 'debit',
            //                 'amount' => $commission,
            //                 'details' => 'Referral commission given to user ' . $referrer->unique_id,
            //             ]);

            //             // অ্যাডমিনকে ইমেইল এবং ডাটাবেস নোটিফিকেশন পাঠানো
            //             $admin->notify(new ReferralCommissionEarned([
            //                 'title' => 'Referral Commission Given',
            //                 'text' => 'User ' . $referrer->unique_id . ' has been given a commission of ' . $commission . ' for user ' . $receiver->unique_id . '.',
            //                 'amount' => $commission,
            //             ]));
            //         }

            //         // কমিশন কাউন্ট আপডেট করা
            //         $referral->increment('commission_count');

            //         // রেফারারকে ইমেইল এবং ডাটাবেস নোটিফিকেশন পাঠানো
            //        $referrer->notify(new ReferralCommissionEarned([
            //             'title' => 'Referral Commission Earned',
            //             'text' => 'You have earned a commission of ' . $commission . ' for the credit received by user ' . $receiver->unique_id . '.',
            //             'amount' => $commission,
            //         ]));
            //     }
            // }
        });

        $this->reset(['mobile', 'amount', 'password', 'receiverData']);
        $this->sendingForm = false;
        $this->confermationForm = false;
        $this->successNotification = true;
    }

    // public function confirmationAction()
    // {
    //     $this->validate([
    //         'password' => ['required']
    //     ]);

    //     $authUser = auth()->user();

    //     if (!Hash::check($this->password, $authUser->password)) {
    //         $this->addError('password', 'Invalid password.');
    //         return;
    //     }

    //     // Perform transaction
    //     $receiver = $this->receiverData;
    //     $amount = $this->amount;

    //     // Start DB transaction if needed for safety
    //     \DB::transaction(function () use ($authUser, $receiver, $amount) {
    //         // Update balances
    //         $authUser->decrement('credit', $amount);
    //         $receiver->increment('credit', $amount);

    //         // Sender transaction
    //         Transaction::create([
    //             'user_id' => $authUser->id,
    //             'type' => 'debit',
    //             'amount' => $amount,
    //             'details' => 'Credit sent to ' . $receiver->unique_id,
    //         ]);

    //         // Receiver transaction
    //         Transaction::create([
    //             'user_id' => $receiver->id,
    //             'type' => 'credit',
    //             'amount' => $amount,
    //             'details' => 'Credit received from ' . $authUser->unique_id,
    //         ]);

    //         // Notify both users
    //         Notification::send($authUser, new CreditTransferred('You sent ' . $amount . ' credits to ' . $receiver->unique_id));
    //         Notification::send($receiver, new CreditTransferred('You received ' . $amount . ' credits from ' . $authUser->unique_id));
    //     });

    //     $this->reset(['mobile', 'amount', 'password', 'receiverData']);
    //     $this->sendingForm = false;
    //     $this->confermationForm = false;
    //     $this->successNotification = true;
    // }



    public function render()
    {
        return view('livewire.frontend.credit-transfer-form')->layout('livewire.layout.frontend.base');
    }
}

<?php

namespace App\Livewire\Backend;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use App\Models\RifleBalanceRequest;
use App\Models\User;
use App\Notifications\RifleRequestSubmitted;
use App\Notifications\RifleRequestUpdated;
use Illuminate\Support\Facades\Notification;
use App\Models\Transaction;
use App\Notifications\RifleAcceptedNotification;
use App\Notifications\RifleCancelledNotification;
use App\Notifications\ReferralCommissionEarned;
use Illuminate\Support\Facades\Mail;
use App\Models\Referral;
use App\Models\ReferralSetting;
use Illuminate\Support\Facades\DB;

class RifleRequestManagementComponent extends Component
{
    use WithPagination, WithFileUploads;

    public $setingsMode=false;

    public $rifleStatus, $sending_method, $sending_mobile, $transaction_id, $amount_rifle, $screenshot, $requestId, $user_id;

    public function setings($id)
    {
        $data=RifleBalanceRequest::find($id);
        $this->sending_method=$data->sending_method;
        $this->sending_mobile=$data->sending_mobile;
        $this->transaction_id=$data->transaction_id;
        $this->amount_rifle=$data->amount_rifle;
        $this->screenshot=$data->screenshot;
        $this->setingsMode=true;
        $this->requestId=$id;
        $this->user_id=$data->user_id;
    }

    // public function accept()
    // {
    //     $data = RifleBalanceRequest::findOrFail($this->requestId);

    //     // 1. স্ট্যাটাস Rifled করার জন্য
    //     $data->status = 'Rifled';
    //     $data->save();

    //     // 2. ইউজারের ক্রেডিট আপডেট করার জন্য
    //     $user = User::findOrFail($this->user_id);
    //     $user->credit += $this->amount_rifle;
    //     $user->save();

    //     // 3. Transaction তৈরির করার জন্য
    //     Transaction::create([
    //         'user_id' => $user->id,
    //         'type' => 'credit',
    //         'amount' => $this->amount_rifle,
    //         'details' => 'Rifle balance accepted',
    //     ]);

    //     // 4. ডাটাবেস ও ইমেইল নোটিফিকেশন পাঠানোর জন্য
    //     $details = [
    //         'title' => 'Rifle Balance Accepted',
    //         'text' => 'Your rifle balance request of ' . $this->amount_rifle . ' has been accepted.',
    //         'amount' => $this->amount_rifle,
    //     ];

    //     $user->notify(new RifleAcceptedNotification($details)); // Database notification
    //     $this->getData();
    //     $this->setingsMode=false;
    // }

    public function accept()
    {
        $data = RifleBalanceRequest::findOrFail($this->requestId);

        \DB::transaction(function () use ($data) {
            // ১. স্ট্যাটাস রাইফলড করার জন্য
            $data->status = 'Rifled';
            $data->save();

            // ২. ইউজারের ক্রেডিট আপডেট করার জন্য
            $user = User::findOrFail($this->user_id);
            $user->credit += $this->amount_rifle;
            $user->save();

            // ৩. ট্রানজাকশন তৈরি করার জন্য
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $this->amount_rifle,
                'details' => 'Rifle balance accepted',
            ]);

            $admin = User::where('role', 'admin')->first();

            if ($admin) {
                        $admin->credit -= $this->amount_rifle;
                        $admin->save();

                        Transaction::create([
                            'user_id' => $admin->id,
                            'type' => 'debit',
                            'amount' => $this->amount_rifle,
                            'details' => 'Rifle balance accepted',
                        ]);
                    }

            // ৪. ডাটাবেস এবং ইমেইল নোটিফিকেশন পাঠানোর জন্য
            $details = [
                'title' => 'Rifle Balance Accepted',
                'text' => 'Your rifle balance request of ' . $this->amount_rifle . ' has been accepted.',
                'amount' => $this->amount_rifle,
            ];

            $user->notify(new RifleAcceptedNotification($details)); // ডাটাবেস নোটিফিকেশন
            // রেফারেল কমিশন হ্যান্ডল করা
            $referral = Referral::where('referred_user_id', $this->user_id)->first();
            if ($referral) {
                $settings = ReferralSetting::first();
                if ($settings && $referral->commission_count < $settings->max_commission_count) {
                    $commission = ($this->amount_rifle * $settings->commission_percentage) / 100;

                    // রেফারারের ক্রেডিট আপডেট করা
                    $referrer = User::find($referral->referrer_id);
                    $referrer->credit += $commission;
                    $referrer->save();

                    // রেফারারের জন্য ট্রানজাকশন তৈরি করা
                    Transaction::create([
                        'user_id' => $referrer->id,
                        'type' => 'credit',
                        'amount' => $commission,
                        //'details' => 'ইউজার ' . $user->unique_id . ' এর জন্য রেফারেল কমিশন',
                        'details' => 'Referral commission for user ' . $user->unique_id,
                    ]);

                    // অ্যাডমিনের জন্য ট্রানজাকশন (ডেবিট)

                    if ($admin) {
                        $admin->credit -= $commission;
                        $admin->save();

                        Transaction::create([
                            'user_id' => $admin->id,
                            'type' => 'debit',
                            'amount' => $commission,
                            //'details' => 'ইউজার ' . $referrer->unique_id . ' কে রেফারেল কমিশন প্রদান',
                            'details' => 'Referral commission given to user ' . $referrer->unique_id,
                        ]);

                        // অ্যাডমিনকে নোটিফিকেশন পাঠানো
                        $admin->notify(new ReferralCommissionEarned([
                            'title' => 'Referral Commission Given',
                            //'text' => 'ইউজার ' . $user->unique_id . ' এর জন্য ' . $referrer->unique_id . ' কে ' . $commission . ' কমিশন প্রদান করা হয়েছে।',
                            'text' => 'User ' . $referrer->unique_id . ' has been given a commission of ' . $commission . ' for user ' . $user->unique_id . '.',
                            'amount' => $commission,
                        ]));
                    }

                    // কমিশন কাউন্ট আপডেট করা
                    $referral->increment('commission_count');

                    // রেফারারকে নোটিফিকেশন পাঠানো
                    $referrer->notify(new ReferralCommissionEarned([
                        'title' => 'Referral Commission Earned',
                        //'text' => 'ইউজার ' . $user->unique_id . ' এর ক্রেডিট যোগের জন্য আপনি ' . $commission . ' কমিশন অর্জন করেছেন।',
                        'text' => 'You have earned a commission of ' . $commission . ' for the credit received by user ' . $user->unique_id . '.',
                        'amount' => $commission,
                    ]));
                }
            }
        });

        $this->getData();
        $this->setingsMode = false;
    }

    public function cancel()
    {
        $data = RifleBalanceRequest::findOrFail($this->requestId);

        // 1. স্ট্যাটাস Cancelled করার জন্য
        $data->status = 'Cancelled';
        $data->save();

        // 2. ইউজারকে নোটিফিকেশন পাঠানোর জন্য
        $user = User::findOrFail($this->user_id);

        $details = [
            'title' => 'Rifle Balance Cancelled',
            'text' => 'Your request has been cancelled due to incorrect information. Please try again with correct details.',
            'amount' => $data->amount_rifle,
            'transaction_id' => $data->transaction_id,
        ];

        $user->notify(new RifleCancelledNotification($details));
        $this->getData();
        $this->setingsMode=false;
    }

    public function mount()
    {
        $this->getData();
    }

    public function getData()
    {
        $this->rifleStatus=RifleBalanceRequest::where('status','Pending')->get();
    }

    public function render()
    {
        return view('livewire.backend.rifle-request-management-component')->layout('livewire.backend.base');
    }
}

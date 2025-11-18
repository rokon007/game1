<?php

namespace App\Livewire\Backend;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use App\Models\WithdrawalRequest;
use App\Models\User;
use App\Notifications\RifleRequestSubmitted;
use App\Notifications\RifleRequestUpdated;
use Illuminate\Support\Facades\Notification;
use App\Models\Transaction;
use App\Notifications\RifleAcceptedNotification;
use App\Notifications\WithdrawalCansel;
use App\Notifications\ReferralCommissionEarned;
use Illuminate\Support\Facades\Mail;
use App\Models\Referral;
use App\Models\ReferralSetting;
use Illuminate\Support\Facades\DB;

class WithdrawalRequests extends Component
{
    use WithPagination, WithFileUploads;

    public $setingsMode=false;


    public $withdrawalStatus, $amount, $method, $account_number, $user_notes, $requestId, $user_id, $user_credit;
    public $allrt=false;
    public function setings($id)
    {
        $data=WithdrawalRequest::find($id);
        $user=User::find($data->user_id);
        $this->method=$data->method;
        $this->account_number=$data->account_number;
        $this->user_notes=$data->user_notes;
        $this->amount=$data->amount;
        $this->setingsMode=true;
        $this->requestId=$id;
        $this->user_id=$data->user_id;
        $this->user_credit=$user->credit;
        if($this->user_credit < $this->amount){
            $this->allrt=true;
        }else{
            $this->allrt=true;
        }
    }



    // public function accept()
    // {
    //     $data = WithdrawalRequest::findOrFail($this->requestId);

    //     \DB::transaction(function () use ($data) {
    //         // ১. স্ট্যাটাস রাইফলড করার জন্য
    //         $data->status = 'approved';
    //         $data->save();

    //         // ২. ইউজারের ক্রেডিট আপডেট করার জন্য
    //         $user = User::findOrFail($this->user_id);
    //        // dd( $user->credit);
    //         $user->credit -= $this->amount;
    //         $user->save();

    //         // ৩. ট্রানজাকশন তৈরি করার জন্য
    //         Transaction::create([
    //             'user_id' => $user->id,
    //             'type' => 'debit',
    //             'amount' => $this->amount,
    //             'details' => 'Withdrawal request approved',
    //         ]);

    //         $admin = User::where('role', 'admin')->first();

    //         if ($admin) {
    //                     $admin->credit += $this->amount;
    //                     $admin->save();

    //                     Transaction::create([
    //                         'user_id' => $admin->id,
    //                         'type' => 'credit',
    //                         'amount' => $this->amount,
    //                         'details' => 'Withdrawal request approved',
    //                     ]);
    //                 }

    //         // ৪. ডাটাবেস এবং ইমেইল নোটিফিকেশন পাঠানোর জন্য
    //         $details = [
    //             'title' => 'Withdrawal request approved',
    //             'text' => 'Your Withdrawal request of ' . $this->amount . ' has been approved.',
    //             'amount' => $this->amount,
    //         ];

    //         $user->notify(new RifleAcceptedNotification($details)); // ডাটাবেস নোটিফিকেশন

    //     });

    //     $this->getData();
    //     $this->setingsMode = false;
    // }

    public function accept()
    {
        $data = WithdrawalRequest::findOrFail($this->requestId);
        $user = User::findOrFail($this->user_id);

        // 🔍 1. প্রথমেই ব্যালেন্স চেক
        if ($user->credit < $this->amount) {

            // স্ট্যাটাস রিজেক্ট করে দাও
            $data->status = 'rejected';
            $data->save();

            // নোটিফিকেশন পাঠাও
            $details = [
                'title' => 'Withdrawal request rejected',
                'text' => 'Your withdrawal request has been rejected due to insufficient balance.',
                'amount' => $this->amount,
            ];

            $user->notify(new RifleAcceptedNotification($details));

            // Livewire-এ মেসেজ দেখানোর জন্য
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Insufficient balance! Withdrawal request rejected.'
            ]);

            return; // ❗ এখানে থামিয়ে দিই — approve অংশ চালানো হবে না
        }

        // 🔄 2. ব্যালেন্স ঠিক থাকলে ট্রানজেকশন চালু হবে
        \DB::transaction(function () use ($data, $user) {

            // স্ট্যাটাস approve
            $data->status = 'approved';
            $data->save();

            // ইউজারের ক্রেডিট হ্রাস
            $user->credit -= $this->amount;
            $user->save();

            // ইউজারের ট্রানজেকশন
            Transaction::create([
                'user_id' => $user->id,
                'type'    => 'debit',
                'amount'  => $this->amount,
                'details' => 'Withdrawal request approved',
            ]);

            // অ্যাডমিন ক্রেডিট আপডেট
            $admin = User::where('role', 'admin')->first();

            if ($admin) {
                $admin->credit += $this->amount;
                $admin->save();

                Transaction::create([
                    'user_id' => $admin->id,
                    'type'    => 'credit',
                    'amount'  => $this->amount,
                    'details' => 'Withdrawal request approved',
                ]);
            }

            // নোটিফিকেশন পাঠানো
            $details = [
                'title' => 'Withdrawal request approved',
                'text'  => 'Your withdrawal request of ' . $this->amount . ' has been approved.',
                'amount'=> $this->amount,
            ];

            $user->notify(new RifleAcceptedNotification($details));
        });

        $this->getData();
        $this->setingsMode = false;
    }


    public function cancel()
    {
        $data = WithdrawalRequest::findOrFail($this->requestId);

        // 1. স্ট্যাটাস Cancelled করার জন্য
        $data->status = 'rejected';
        $data->save();

        // 2. ইউজারকে নোটিফিকেশন পাঠানোর জন্য
        $user = User::findOrFail($this->user_id);

        $details = [
            'title' => 'Withdrawal request rejected',
            'text' => 'Your request has been cancelled due to incorrect information. Please try again with correct details.',
            'amount' => $data->amount,
        ];

        $user->notify(new WithdrawalCansel($details));
        $this->getData();
        $this->setingsMode=false;
    }

    public function mount()
    {
        $this->getData();
    }

    public function getData()
    {
        $this->withdrawalStatus=WithdrawalRequest::where('status','Pending')->get();
    }

    public function render()
    {
        return view('livewire.backend.withdrawal-requests')->layout('livewire.backend.base');
    }
}

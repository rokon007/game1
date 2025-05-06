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
use Illuminate\Support\Facades\Mail;

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

    public function accept()
    {
        $data = RifleBalanceRequest::findOrFail($this->requestId);

        // 1. স্ট্যাটাস Rifled করার জন্য
        $data->status = 'Rifled';
        $data->save();

        // 2. ইউজারের ক্রেডিট আপডেট করার জন্য
        $user = User::findOrFail($this->user_id);
        $user->credit += $this->amount_rifle;
        $user->save();

        // 3. Transaction তৈরির করার জন্য
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'credit',
            'amount' => $this->amount_rifle,
            'details' => 'Rifle balance accepted',
        ]);

        // 4. ডাটাবেস ও ইমেইল নোটিফিকেশন পাঠানোর জন্য
        $details = [
            'title' => 'Rifle Balance Accepted',
            'text' => 'Your rifle balance request of ' . $this->amount_rifle . ' has been accepted.',
            'amount' => $this->amount_rifle,
        ];

        $user->notify(new RifleAcceptedNotification($details)); // Database notification
        $this->getData();
        $this->setingsMode=false;
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

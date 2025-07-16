<?php

namespace App\Livewire\Backend\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CreditTransferred;

class UserComponent extends Component
{
    use WithPagination;

    public string $search = '';
    public $userId, $name, $unique_id, $oldUnique_id, $email, $mobile, $avatar, $credit, $status, $is_online, $last_login_location;
    public $changeIdModel=false;
    public $changeStatusModal = false;
    public $amountMode=true;
    public $confirmMode=false;
    public $dUserId;
    public $details;
    public $transactionSuccess=false;
    public $amount;
    public $deductionModel, $password;
    public $rechargeModal=false;


    public function changeId($id)
    {
        $this->userId=$id;
        $user=User::find($id);
        $this->name=$user->name;
        $this->oldUnique_id=$user->unique_id;
        $this->email=$user->email;
        $this->mobile=$user->mobile;
        $this->avatar=$user->avatar;
        $this->credit=$user->credit;
        $this->status=$user->status;
        $this->is_online=$user->is_online;
        $this->last_login_location=$user->last_login_location;
        $this->changeIdModel=true;
        $this->unique_id='';
        $this->dispatch('openChangeIdModal');
    }

    public function  deduction($id)
    {
        //dd($id);
        $this->dUserId=$id;
        $user=User::find($id);
        $this->name=$user->name;
        $this->oldUnique_id=$user->unique_id;
        $this->email=$user->email;
        $this->mobile=$user->mobile;
        $this->avatar=$user->avatar;
        $this->credit=$user->credit;
        $this->status=$user->status;
        $this->is_online=$user->is_online;
        $this->last_login_location=$user->last_login_location;
        $this->unique_id=$user->unique_id;
        $this->rechargeModal=true;
        $this->dispatch('openDeductionModel');
    }


    public function rechargeNext()
    {
        $this->validate([
            'amount' => ['required'],
            'details' => ['required']
        ]);
        $authUser = User::find($this->dUserId);
        if ($this->amount > $authUser->credit) {
            $this->addError('amount', 'Insufficient balance for this transfer.');
            return;
        }

        $this->amountMode=false;
        $this->confirmMode=true;
    }

    public function comfirm()
    {
        $id=$this->dUserId;
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
            $authUser->increment('credit', $amount);
            $receiver->decrement('credit', $amount);

            // Sender transaction
            Transaction::create([
                'user_id' => $authUser->id,
                'type' => 'credit',
                'amount' => $amount,
                'details' => $this->details,
            ]);

            // Receiver transaction
            Transaction::create([
                'user_id' => $receiver->id,
                'type' => 'debit',
                'amount' => $amount,
                'details' => $this->details,
            ]);

            // Notify both users
            Notification::send($authUser, new CreditTransferred('You received ' . $amount . ' credits from ' . $receiver->unique_id));
            Notification::send($receiver, new CreditTransferred('Yours credit have deducted ' . $amount . ' credits to ' . $authUser->name));
        });

        $this->closeRechargeModal();
        $this->transactionSuccess = true;
    }

    public function closeRechargeModal()
    {
        $this->reset(['amount', 'details', 'password']);
        $this->amountMode=true;
        $this->confirmMode=false;
        $this->rechargeModal=false;
        $this->dispatch('closeDeductionModel');
    }

    public function generateUniqueId()
    {
        do {
            // দুইটি র‍্যান্ডম অক্ষর + ৫টি সংখ্যা
            $randomLetters = strtoupper(chr(rand(65, 90)) . chr(rand(65, 90))); // A-Z
            $randomDigits = rand(10000, 99999);
            $generatedId = $randomLetters . $randomDigits;
        } while (\App\Models\User::where('unique_id', $generatedId)->exists());

        $this->unique_id = $generatedId;
    }


    public function updateUniqueId()
    {
        $this->validate([
            'unique_id' => 'required',
        ]);

        $user = User::find($this->userId);
        $user->unique_id = $this->unique_id;
        $user->save();

        session()->flash('success', 'Unique ID updated successfully!');
        $this->changeIdModel = false;
        $this->dispatch('closeChangeIdModal');
    }

    public function changeStatus($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $this->userId = $user->id;
        $this->status = $user->status;
        $this->changeStatusModal = true;

        $this->dispatch('openChangeStatusModal');
    }

    public function updateStatus()
    {
        $this->validate([
            'status' => 'required|in:active,banned,pending',
        ]);

        $user = \App\Models\User::findOrFail($this->userId);
        $user->status = $this->status;
        $user->save();

        session()->flash('success', 'User status updated successfully.');
        $this->changeStatusModal = false;
        $this->dispatch('closeChangeStatusModal');
    }




    public function updatingSearch()
    {
        $this->resetPage(); // সার্চ আপডেট হলে পেজ ১-এ ফিরে যাবে
    }

    public function render()
    {
        $users = User::query()
            ->where(function ($query) {
                $query->where('unique_id', 'like', '%' . $this->search . '%')
                    ->orWhere('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('mobile', 'like', '%' . $this->search . '%')
                    ->orWhere('last_login_location', 'like', '%' . $this->search . '%');
            })
            ->select('id', 'unique_id', 'name', 'email', 'mobile', 'last_login_location','is_online','status','credit','avatar')
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.backend.user.user-component', [
            'users' => $users,
        ])->layout('livewire.backend.base');
    }
}

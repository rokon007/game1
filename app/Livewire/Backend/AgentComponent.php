<?php

namespace App\Livewire\Backend;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CreditTransferred;

class AgentComponent extends Component
{
     use WithPagination;

    public string $search = '';
    public $agentMModal=false;
    public $name, $email, $mobile, $avatar, $role, $credit, $status, $is_online, $user_id;
    public $agents;
    public $rechargeModal=false;
    public $rechargeUser_id;
    public $rechargeUser;
    public $amountMode=false;
    public $confirmMode=false;
    public $amount;
    public $password;
    public $transactionSuccess=false;

    public function updatingSearch()
    {
        $this->resetPage(); // Reset to page 1 when search updated
    }

    public function mount()
    {
        $this->getAgent();
    }

    public function openManagmentModal($id)
    {
        $user=User::find($id);
        $this->user_id=$id;
        $this->name=$user->name;
        $this->email=$user->email;
        $this->mobile=$user->mobile;
        $this->avatar=$user->avatar;
        $this->role=$user->role;
        $this->credit=$user->credit;
        $this->status=$user->status;
        $this->is_online=$user->is_online;
        $this->agentMModal=true;
    }

    public function openRechargeModal($id)
    {
        $this->rechargeUser_id=$id;
        $this->rechargeUser = User::findOrFail($this->rechargeUser_id);
        $this->amountMode=true;
        $this->rechargeModal=true;
    }

    public function rechargeNext()
    {
        $this->validate([
            'amount' => ['required']
        ]);
        $authUser = auth()->user();
        if ($this->amount > $authUser->credit) {
            $this->addError('amount', 'Insufficient balance for this transfer.');
            return;
        }

        $this->amountMode=false;
        $this->confirmMode=true;
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
            $authUser->decrement('credit', $amount);
            $receiver->increment('credit', $amount);

            // Sender transaction
            Transaction::create([
                'user_id' => $authUser->id,
                'type' => 'debit',
                'amount' => $amount,
                'details' => 'Credit sent to ' . $receiver->name,
            ]);

            // Receiver transaction
            Transaction::create([
                'user_id' => $receiver->id,
                'type' => 'credit',
                'amount' => $amount,
                'details' => 'Credit received from ' . $authUser->name,
            ]);

            // Notify both users
            Notification::send($authUser, new CreditTransferred('You sent ' . $amount . ' credits to ' . $receiver->name));
            Notification::send($receiver, new CreditTransferred('You received ' . $amount . ' credits from ' . $authUser->name));
        });

        $this->closeRechargeModal();
        $this->transactionSuccess = true;
    }

    public function closeRechargeModal()
    {
        $this->reset(['amount', 'password']);
        $this->amountMode=false;
        $this->confirmMode=false;
        $this->amountMode=false;
        $this->rechargeModal=false;
    }

    public function updateUser()
    {
        $this->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:user,agent',
            'status' => 'required|in:active,banned',
        ]);

        $user = User::findOrFail($this->user_id);

        $user->role = $this->role;
        $user->status = $this->status;
        $user->save();

        session()->flash('success', 'User updated successfully.');

        // Close modal (if using modal flag)
        $this->agentMModal=false;

        // Optional: refresh users list or reset form
        $this->reset(['user_id', 'role', 'status']);
        $this->getAgent();
    }

    public function getAgent()
    {
        $this->agents = User::where('role','agent')
        ->orderBy('name')
        ->get();
    }

    public function render()
    {
        $users = User::where('role','user')
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('mobile', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.backend.agent-component', compact('users'))->layout('livewire.backend.base');
    }
}

<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\WithdrawalRequest;
use App\Models\User;
use App\Notifications\WithdrawalRequestSubmitted;
use App\Notifications\WithdrawalRequestUpdated;
use Illuminate\Support\Facades\Notification;

class WithdrawalForm extends Component
{
    use WithPagination;

    public $amount, $method, $account_number, $user_notes;
    public $ruleSection = true;
    public $paymentMethodSection = false;
    public $submitSection = false;
    public $requestStatus = false;
    public $withdrawalStatus = [];
    public $edit_id = null;

    // Minimum and maximum withdrawal amounts
    public $min_amount = 100;
    public $max_amount = 50000;

    public function mount()
    {
        $userId = auth()->user()->id;
        $this->withdrawalStatus = WithdrawalRequest::where('user_id', $userId)
            ->whereIn('status', ['pending'])
            ->get();
        $this->account_number=auth()->user()->mobile;
        // If user has pending requests, show status
        if ($this->withdrawalStatus->count() > 0) {
            $this->ruleSection = false;
            $this->requestStatus = true;
        }
    }

    public function newRequest()
    {
        $this->ruleSection = true;
        $this->paymentMethodSection = false;
        $this->submitSection = false;
        $this->requestStatus = false;
        $this->reset(['amount', 'method', 'account_number', 'user_notes']);
    }

    public function nextToPaymentMethod()
    {
        $this->validate([
            'amount' => 'required|numeric|min:' . $this->min_amount . '|max:' . $this->max_amount,
        ]);

        // Check if user has sufficient balance
        if (auth()->user()->credit < $this->amount) {
            session()->flash('error', 'Insufficient balance. Your current balance is ' . auth()->user()->credit . '৳');
            return;
        }

        $this->ruleSection = false;
        $this->paymentMethodSection = true;
    }

    public function selectMethod($selectedMethod)
    {
        $this->method = $selectedMethod;
        $this->paymentMethodSection = false;
        $this->submitSection = true;
    }

    protected function rules()
    {
        return [
            'amount' => 'required|numeric|min:' . $this->min_amount . '|max:' . $this->max_amount,
            'method' => 'required|string|in:bKash,Nagad,Rocket,Upay',
            'account_number' => 'required|string|max:255',
            'user_notes' => 'nullable|string|max:500',
        ];
    }

    public function submitWithdrawalRequest()
    {
        $this->validate();

        // Check balance again before submission
        if (auth()->user()->credit < $this->amount) {
            session()->flash('error', 'Insufficient balance. Your current balance is ' . auth()->user()->credit . '৳');
            return;
        }

        DB::transaction(function () {
            // Create withdrawal request
            $withdrawal = WithdrawalRequest::create([
                'user_id' => auth()->user()->id,
                'amount' => $this->amount,
                'method' => $this->method,
                'account_number' => $this->account_number,
                'user_notes' => $this->user_notes,
                'status' => 'pending',
            ]);

            // Deduct amount from user's credit
            //auth()->user()->decrement('credit', $this->amount);

            // Prepare notification data
            $data = [
                'user_name' => auth()->user()->name,
                'user_id' => auth()->user()->id,
                'amount' => $this->amount,
                'method' => $this->method,
                'request_id' => $withdrawal->id,
            ];

            // Notify user
            auth()->user()->notify(new WithdrawalRequestSubmitted($data));

            // Notify admins
            $admins = User::where('role', 'admin')->get();
            Notification::send($admins, new WithdrawalRequestSubmitted($data));
        });

        // Reset and show status
        $this->submitSection = false;
        $this->requestStatus = true;
        $this->withdrawalStatus = WithdrawalRequest::where('user_id', auth()->user()->id)
            ->whereIn('status', ['pending'])
            ->get();

        session()->flash('success', 'Withdrawal request submitted successfully!');
        $this->reset(['amount', 'method', 'account_number', 'user_notes']);
    }

    public function cancelRequest($id)
    {
        DB::transaction(function () use ($id) {
            $withdrawal = WithdrawalRequest::where('id', $id)
                ->where('user_id', auth()->user()->id)
                ->where('status', 'pending')
                ->firstOrFail();

            // Return amount to user's credit
            auth()->user()->increment('credit', $withdrawal->amount);

            // Update status to rejected (cancelled by user)
            $withdrawal->update(['status' => 'rejected', 'admin_notes' => 'Cancelled by user']);

            // Notify admins about cancellation
            $data = [
                'user_name' => auth()->user()->name,
                'user_id' => auth()->user()->id,
                'amount' => $withdrawal->amount,
                'method' => $withdrawal->method,
                'request_id' => $withdrawal->id,
            ];

            $admins = User::where('role', 'admin')->get();
            Notification::send($admins, new WithdrawalRequestSubmitted($data));
        });

        $this->withdrawalStatus = WithdrawalRequest::where('user_id', auth()->user()->id)
            ->whereIn('status', ['pending'])
            ->get();

        session()->flash('success', 'Withdrawal request cancelled successfully!');
    }

    public function render()
    {
        return view('livewire.frontend.withdrawal-form')->layout('livewire.layout.frontend.base');
    }
}

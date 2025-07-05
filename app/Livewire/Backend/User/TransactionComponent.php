<?php

namespace App\Livewire\Backend\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;

class TransactionComponent extends Component
{
    use WithPagination;

    public $userId;
    public $startDate;
    public $endDate;
    public $type = ''; // credit/debit/all

    public function mount($id)
    {
        $this->userId = $id;
    }

    public function updatingStartDate() { $this->resetPage(); }
    public function updatingEndDate() { $this->resetPage(); }
    public function updatingType() { $this->resetPage(); }

    public function render()
    {
        $transactions = Transaction::where('user_id', $this->userId)
            ->when($this->startDate, fn($q) => $q->whereDate('created_at', '>=', Carbon::parse($this->startDate)))
            ->when($this->endDate, fn($q) => $q->whereDate('created_at', '<=', Carbon::parse($this->endDate)))
            ->when(in_array($this->type, ['credit', 'debit']), fn($q) => $q->where('type', $this->type))
            ->latest()
            ->paginate(10);

        $user = User::find($this->userId);

        return view('livewire.backend.user.transaction-component', [
            'transactions' => $transactions,
            'user' => $user,
        ])->layout('livewire.backend.base');
    }
}

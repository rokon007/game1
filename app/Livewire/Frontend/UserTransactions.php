<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;

class UserTransactions extends Component
{
    use WithPagination;

    public $search = '';
    public $detailsMode = false;
    public $selectedTransactions;

    public function details($id)
    {
        $this->selectedTransactions = Transaction::find($id);
        $this->detailsMode = true;

    }

    public function backToList()
    {
        $this->detailsMode = false;
        $this->selectedTransactions = null;
    }

    // Pagination styling (optional, if using Bootstrap)
    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage(); // When search changes, reset to page 1
    }

    public function render()
    {
        $user = Auth::user();

        $transactions = Transaction::where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('details', 'like', '%' . $this->search . '%')
                      ->orWhere('amount', 'like', '%' . $this->search . '%')
                      ->orWhere('type', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.frontend.user-transactions', [
            'transactions' => $transactions,
        ])->layout('livewire.layout.frontend.base');
    }
}

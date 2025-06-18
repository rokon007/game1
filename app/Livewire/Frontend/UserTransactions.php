<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use App\Models\Transaction;

class UserTransactions extends Component
{
    public $transactions = [];
    public $detailsMode = false;
    public $selectedTransactions;

    public $perPage = 10;
    public $loadedCount = 0;

    public function mount()
    {
        $this->loadTransactions();
    }

    public function loadTransactions()
    {
        // ব্যবহারকারী প্রমাণীকৃত কিনা চেক করা
        if (!Auth::check()) {
            $this->transactions = [];
            return;
        }

        $user = Auth::user();

        // প্রাথমিক নোটিফিকেশন লোড: নতুন থেকে পুরানো
        $this->transactions=Transaction::where('user_id', $user->id)
                            ->orderBy('created_at', 'desc')
                            ->take($this->perPage)
                            ->get();

        $this->loadedCount = $this->transactions->count();

    }

    #[On('loadMore')]
    public function loadMore()
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        // নতুন নোটিফিকেশন লোড
        $newTransactions = Transaction::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->skip($this->loadedCount)
                        ->take($this->perPage)
                        ->get();

        // তালিকার শেষে নতুন নোটিফিকেশন যোগ
        $this->transactions = $this->transactions->merge($newTransactions);
        $this->loadedCount += $newTransactions->count();
    }

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
        return view('livewire.frontend.user-transactions')->layout('livewire.layout.frontend.base');
    }
}

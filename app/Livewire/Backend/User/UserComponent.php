<?php

namespace App\Livewire\Backend\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;

class UserComponent extends Component
{
    use WithPagination;

    public string $search = '';
    public $userId, $name, $unique_id, $oldUnique_id, $email, $mobile, $avatar, $credit, $status, $is_online, $last_login_location;
    public $changeIdModel=false;
    public $changeStatusModal = false;


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

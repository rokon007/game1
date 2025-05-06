<?php

namespace App\Livewire\Frontend;

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

class RifleComponent extends Component
{
    use WithPagination, WithFileUploads;
    public $screenshot, $photo1, $amount_rifle, $sending_mobile, $sending_method, $status, $transaction_id, $rifleStatus, $delet_id;
    public $ruleSection=true;
    public $paymentMethodSection=false;
    public $submitSection=false;
    public $requestStatus=false;
    public $deletModal=false;
    public $paymentMethod='';
    public $data_id=false;

    public function resubmit($id)
    {
        $this->data_id=$id;
        $data = RifleBalanceRequest::findOrFail($this->data_id);
        $this->amount_rifle=$data->amount_rifle;
        $this->sending_mobile=$data->sending_mobile;
        $this->sending_method=$data->sending_method;
        $this->status=$data->status;
        $this->transaction_id=$data->transaction_id;
        $this->screenshot=$data->screenshot;

        $this->submitSection=true;
        $this->ruleSection=false;
        $this->paymentMethodSection=false;
        $this->requestStatus=false;
        $this->deletModal=false;
    }

    public function delet($id)
    {
        $this->delet_id=$id;
        $this->deletModal=true;
    }

    public function deletData()
    {
        try {
            // ডেটা খোঁজা ও ডিলিট
            $data = RifleBalanceRequest::findOrFail($this->delet_id);
            $data->delete();

            // modal বন্ধ করা ও ফিল্ড reset করা
            $this->deletModal = false;
            $this->delet_id = null;

            // success মেসেজ
            session()->flash('error', 'Data has been deleted successfully.');
            $this->rifle_status();
        } catch (\Exception $e) {
            // error মেসেজ
            session()->flash('error', 'Something went wrong while deleting.');
        }
    }

    public function newRequest()
    {
        $this->ruleSection=true;
        $this->paymentMethodSection=false;
        $this->submitSection=false;
        $this->requestStatus=false;
    }

    public function mount()
    {
        $this->sending_mobile=auth()->user()->mobile;
        $userId=auth()->user()->id;
        $statuses = RifleBalanceRequest::where('user_id', $userId)->pluck('status')->toArray();

        if (in_array('Pending', $statuses) || in_array('Cancelled', $statuses)) {
            $this->rifle_status();
            $this->ruleSection=false;
            $this->paymentMethodSection=false;
            $this->submitSection=false;
            $this->requestStatus=true;
            $this->deletModal=false;
        }

    }

    public function nextToPaymentMethod()
    {
        $this->ruleSection=false;
        $this->paymentMethodSection=true;
    }
    public function paymentBikash()
    {
        $this->paymentMethod='Bikash';
        $this->sending_method=$this->paymentMethod;
        $this->paymentMethodSection=false;
        $this->submitSection=true;
    }
    public function paymentNagad()
    {
        $this->paymentMethod='Nagad';
        $this->sending_method=$this->paymentMethod;
        $this->paymentMethodSection=false;
        $this->submitSection=true;
    }
    public function paymentRoket()
    {
        $this->paymentMethod='Roket';
        $this->sending_method=$this->paymentMethod;
        $this->paymentMethodSection=false;
        $this->submitSection=true;
    }
    public function paymentUpay()
    {
        $this->paymentMethod='Upay';
        $this->sending_method=$this->paymentMethod;
        $this->paymentMethodSection=false;
        $this->submitSection=true;
    }

    // protected $rules = [
    //     'amount_rifle' => 'required|string|max:255',
    //     'sending_mobile' => 'required|string|max:255',
    //     'sending_method' => 'required|string|max:255',
    //     'photo1' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024',
    //     'transaction_id' => 'required|string|max:255',
    // ];

    protected function rules()
    {
        $rules = [
            'amount_rifle' => 'required|string|max:255',
            'sending_mobile' => 'required|string|max:255',
            'sending_method' => 'required|string|max:255',
            'transaction_id' => 'required|string|max:255',
        ];

        // যদি data_id না থাকে (মানে create হচ্ছে), তাহলে photo1 বাধ্যতামূলক
        if (!$this->data_id) {
            $rules['photo1'] = 'required|image|mimes:jpeg,png,jpg,gif|max:1024';
        } else {
            $rules['photo1'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024';
        }

        return $rules;
    }


    public function saveRifleRequests()
    {
        $this->validate();

        $imagePath = null;
        if ($this->photo1) {
            $imageName = uniqid().'.'.$this->photo1->getClientOriginalExtension();
            $filePath = "screenshot/{$imageName}";

            // সরাসরি ইমেজ স্টোর করুন (রিসাইজ ছাড়া)
            $this->photo1->storeAs('public', $filePath);
            $imagePath = $filePath;
        }

        RifleBalanceRequest::create([
            'user_id' => auth()->user()->id,
            'amount_rifle' => $this->amount_rifle,
            'sending_mobile' => $this->sending_mobile,
            'screenshot' => $imagePath,
            'sending_method' => $this->sending_method,
            'status' => 'Pending',
            'transaction_id' => $this->transaction_id,
        ]);

        $this->paymentMethodSection=false;
        $this->submitSection=false;
        $this->requestStatus=true;
        $this->dispatch("sentRifleRequest");
        session()->flash('error', 'Data has been deleted successfully.');
        $this->rifle_status();

        // 🔔 নোটিফিকেশন পাঠানো
        $data = [
            'user_name' => auth()->user()->name,
            'amount_rifle' => $this->amount_rifle,
            'sending_method' => $this->sending_method,
            'sending_mobile' => $this->sending_mobile,
            'transaction_id' => $this->transaction_id,
            'admin_link' => "#",
        ];

        // ইউজারকে নোটিফাই করুন
        auth()->user()->notify(new RifleRequestSubmitted($data));

        // অ্যাডমিনদের নোটিফাই করুন (যাদের role = 'admin')
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new RifleRequestSubmitted($data));
        }
        $this->reset(['photo1', 'amount_rifle', 'sending_mobile', 'sending_method', 'transaction_id']);
    }

    public function updateRifleRequests()
    {
        $this->validate();

        // যেই রিকোর্ড আপডেট করতে হবে সেটা ধরুন (এখানে ধরে নিচ্ছি আপনি id বা model লোড করেছেন)
        $request = RifleBalanceRequest::findOrFail($this->data_id);

        // পুরানো ইমেজ ডিলিট এবং নতুন ইমেজ আপলোড
        $imagePath = $request->screenshot;

        if ($this->photo1) {
            // পুরানো ইমেজ ডিলিট
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            // নতুন ইমেজ আপলোড
            $imageName = uniqid() . '.' . $this->photo1->getClientOriginalExtension();
            $filePath = "screenshot/{$imageName}";
            $this->photo1->storeAs('public', $filePath);
            $imagePath = $filePath;

            // ডেটা আপডেট
            $request->update([
                'amount_rifle' => $this->amount_rifle,
                'sending_mobile' => $this->sending_mobile,
                'screenshot' => $imagePath,
                'sending_method' => $this->sending_method,
                'status' => 'Pending',
                'transaction_id' => $this->transaction_id,
            ]);
        }else{
            // ডেটা আপডেট
            $request->update([
                'amount_rifle' => $this->amount_rifle,
                'sending_mobile' => $this->sending_mobile,
                'sending_method' => $this->sending_method,
                'status' => 'Pending',
                'transaction_id' => $this->transaction_id,
            ]);
        }



        // রিসেট ও রিফ্রেশ

        $this->dispatch('updatedRifleRequest');
        session()->flash('success', 'Request has been updated successfully.');
        $this->rifle_status();

        // ইউজার ও অ্যাডমিনকে খুঁজে বের করা
        $user = auth()->user();
        $admin = User::where('role', 'admin')->first(); // অথবা আপনার উপযুক্ত অ্যাডমিন সিলেকশন লজিক

        // Notification Data
        $notificationData = [
            'title' => 'Rifle Balance Request Updated',
            'user' => $user->name,
            'amount' => $this->amount_rifle,
            'method' => $this->sending_method,
            'transaction_id' => $this->transaction_id,
            'admin_link' => "#",
        ];

        // ইউজার ও অ্যাডমিনকে নোটিফিকেশন পাঠানো
        Notification::send([$user, $admin], new RifleRequestUpdated($notificationData));

        $this->reset(['photo1', 'amount_rifle', 'sending_mobile', 'sending_method', 'transaction_id']);
    }

    public function rifle_status()
    {
        $userId=auth()->user()->id;
        $this->rifleStatus=RifleBalanceRequest::where('user_id',$userId)->whereIn('status', ['Pending', 'Cancelled'])->get();
    }

    public function render()
    {
        return view('livewire.frontend.rifle-component')->layout('livewire.layout.frontend.base');
    }
}

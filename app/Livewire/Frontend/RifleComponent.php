<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use App\Models\RifleBalanceRequest;
use App\Models\User;
use App\Models\RefillSetting; // নতুন মডেল যোগ করুন
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

    // Refill settings properties
    public $refillSettings;
    public $bikash_number, $nagad_number, $rocket_number, $upay_number;

    public function mount()
    {
        $this->sending_mobile = auth()->user()->mobile;
        $userId = auth()->user()->id;
        $statuses = RifleBalanceRequest::where('user_id', $userId)->pluck('status')->toArray();

        // Refill settings লোড করুন
        $this->loadRefillSettings();

        if (in_array('Pending', $statuses) || in_array('Cancelled', $statuses)) {
            $this->rifle_status();
            $this->ruleSection=false;
            $this->paymentMethodSection=false;
            $this->submitSection=false;
            $this->requestStatus=true;
            $this->deletModal=false;
        }
    }

    // Refill settings লোড করার মেথড
    private function loadRefillSettings()
    {
        $this->refillSettings = RefillSetting::where('is_active', true)->first();

        if ($this->refillSettings) {
            $this->bikash_number = $this->refillSettings->bikash_number;
            $this->nagad_number = $this->refillSettings->nagad_number;
            $this->rocket_number = $this->refillSettings->rocket_number;
            $this->upay_number = $this->refillSettings->upay_number;
        } else {
            // Default numbers if no settings found
            $this->bikash_number = '01711111111';
            $this->nagad_number = '01711111111';
            $this->rocket_number = '01711111111';
            $this->upay_number = '01711111111';
        }
    }

    // Payment method selection methods - আপডেট করুন
    public function paymentBikash()
    {
        $this->paymentMethod = 'Bikash';
        $this->sending_method = $this->paymentMethod;
        $this->paymentMethodSection = false;
        $this->submitSection = true;
    }

    public function paymentNagad()
    {
        $this->paymentMethod = 'Nagad';
        $this->sending_method = $this->paymentMethod;
        $this->paymentMethodSection = false;
        $this->submitSection = true;
    }

    public function paymentRoket()
    {
        $this->paymentMethod = 'Roket';
        $this->sending_method = $this->paymentMethod;
        $this->paymentMethodSection = false;
        $this->submitSection = true;
    }

    public function paymentUpay()
    {
        $this->paymentMethod = 'Upay';
        $this->sending_method = $this->paymentMethod;
        $this->paymentMethodSection = false;
        $this->submitSection = true;
    }

    // আপনার existing methods (resubmit, delet, newRequest, nextToPaymentMethod, saveRifleRequests, updateRifleRequests, rifle_status) একই থাকবে
    // ... existing methods ...

    public function resubmit($id)
    {
        $this->data_id = $id;
        $data = RifleBalanceRequest::findOrFail($this->data_id);
        $this->amount_rifle = $data->amount_rifle;
        $this->sending_mobile = $data->sending_mobile;
        $this->sending_method = $data->sending_method;
        $this->status = $data->status;
        $this->transaction_id = $data->transaction_id;
        $this->screenshot = $data->screenshot;

        $this->submitSection = true;
        $this->ruleSection = false;
        $this->paymentMethodSection = false;
        $this->requestStatus = false;
        $this->deletModal = false;
    }

    public function delet($id)
    {
        $this->delet_id = $id;
        $this->deletModal = true;
    }

    public function deletData()
    {
        try {
            $data = RifleBalanceRequest::findOrFail($this->delet_id);
            $data->delete();

            $this->deletModal = false;
            $this->delet_id = null;

            session()->flash('error', 'Data has been deleted successfully.');
            $this->rifle_status();
        } catch (\Exception $e) {
            session()->flash('error', 'Something went wrong while deleting.');
        }
    }

    public function newRequest()
    {
        $this->ruleSection = true;
        $this->paymentMethodSection = false;
        $this->submitSection = false;
        $this->requestStatus = false;
    }

    public function nextToPaymentMethod()
    {
        $this->ruleSection = false;
        $this->paymentMethodSection = true;
    }

    protected function rules()
    {
        $rules = [
            'amount_rifle' => 'required|string|max:255',
            'sending_mobile' => 'required|string|max:255',
            'sending_method' => 'required|string|max:255',
            'transaction_id' => 'required|string|max:255',
        ];

        if (!$this->data_id) {
            $rules['photo1'] = 'nullable|image|mimes:jpeg,png,jpg,gif';
        } else {
            $rules['photo1'] = 'nullable|image|mimes:jpeg,png,jpg,gif';
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

        $this->paymentMethodSection = false;
        $this->submitSection = false;
        $this->requestStatus = true;
        $this->dispatch("sentRifleRequest");
        session()->flash('error', 'Data has been deleted successfully.');
        $this->rifle_status();

        $data = [
            'user_name' => auth()->user()->name,
            'amount_rifle' => $this->amount_rifle,
            'sending_method' => $this->sending_method,
            'sending_mobile' => $this->sending_mobile,
            'transaction_id' => $this->transaction_id,
            'admin_link' => "#",
        ];

        auth()->user()->notify(new RifleRequestSubmitted($data));
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new RifleRequestSubmitted($data));
        }

        $this->reset(['photo1', 'amount_rifle', 'sending_mobile', 'sending_method', 'transaction_id']);
    }

    public function updateRifleRequests()
    {
        $this->validate();

        $request = RifleBalanceRequest::findOrFail($this->data_id);
        $imagePath = $request->screenshot;

        if ($this->photo1) {
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            $imageName = uniqid() . '.' . $this->photo1->getClientOriginalExtension();
            $filePath = "screenshot/{$imageName}";
            $this->photo1->storeAs('public', $filePath);
            $imagePath = $filePath;

            $request->update([
                'amount_rifle' => $this->amount_rifle,
                'sending_mobile' => $this->sending_mobile,
                'screenshot' => $imagePath,
                'sending_method' => $this->sending_method,
                'status' => 'Pending',
                'transaction_id' => $this->transaction_id,
            ]);
        } else {
            $request->update([
                'amount_rifle' => $this->amount_rifle,
                'sending_mobile' => $this->sending_mobile,
                'sending_method' => $this->sending_method,
                'status' => 'Pending',
                'transaction_id' => $this->transaction_id,
            ]);
        }

        $this->dispatch('updatedRifleRequest');
        session()->flash('success', 'Request has been updated successfully.');
        $this->rifle_status();

        $user = auth()->user();
        $admin = User::where('role', 'admin')->first();

        $notificationData = [
            'title' => 'Rifle Balance Request Updated',
            'user' => $user->name,
            'amount' => $this->amount_rifle,
            'method' => $this->sending_method,
            'transaction_id' => $this->transaction_id,
            'admin_link' => "#",
        ];

        Notification::send([$user, $admin], new RifleRequestUpdated($notificationData));
        $this->reset(['photo1', 'amount_rifle', 'sending_mobile', 'sending_method', 'transaction_id']);
    }

    public function rifle_status()
    {
        $userId = auth()->user()->id;
        $this->rifleStatus = RifleBalanceRequest::where('user_id', $userId)->whereIn('status', ['Pending', 'Cancelled'])->get();
    }

    public function render()
    {
        return view('livewire.frontend.rifle-component')->layout('livewire.layout.frontend.base');
    }
}

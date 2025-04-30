<?php

namespace App\Livewire\Backend\Prize;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Prize;

class ManagePrize extends Component
{
    use WithPagination, WithFileUploads;

    public $name, $amount, $description, $image_path, $is_active = true, $prize_id;
    public $search = '';

    public function store()
    {
        $this->validate([
            'name' => 'required|string',
            'amount' => 'required|integer|min:0',
            'image_path' => 'image|max:1024|nullable',
        ]);

        $imagePath = null;

        // Check if new image is uploaded
        if ($this->image_path) {
            // Delete the old image if it exists
            if ($this->prize_id) {
                $prize = Prize::find($this->prize_id);
                if ($prize && $prize->image_path) {
                    Storage::delete($prize->image_path);
                }
            }

            // Save the original image without resizing
            $imageName = uniqid() . '.' . $this->image_path->getClientOriginalExtension();
            $filePath = "public/prize/{$imageName}";
            $this->image_path->storeAs('public/prize', $imageName);

            $imagePath = $filePath;
        }

        Prize::updateOrCreate(
            ['id' => $this->prize_id],
            [
                'name' => $this->name,
                'amount' => $this->amount,
                'description' => $this->description,
                'is_active' => $this->is_active,
                'image_path' => $imagePath ?? ($this->prize_id ? Prize::find($this->prize_id)->image_path : null),
            ]
        );

        session()->flash('message', $this->prize_id ? 'Prize Updated Successfully' : 'Prize Created Successfully');
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $prize = Prize::findOrFail($id);
        $this->prize_id = $prize->id;
        $this->name = $prize->name;
        $this->amount = $prize->amount;
        $this->description = $prize->description;
        $this->is_active = $prize->is_active;
    }

    public function delete($id)
    {
        $prize = Prize::find($id);
        if ($prize) {
            if ($prize->image_path) {
                Storage::delete($prize->image_path);
            }
            $prize->delete();
            session()->flash('message', 'Prize Deleted Successfully');
        }
    }

    private function resetInputFields()
    {
        $this->reset(['prize_id', 'name', 'amount', 'description', 'image_path']);
        $this->is_active = true;
    }

    public function render()
    {
        $prizes = Prize::when($this->search, function($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->latest()
            ->get();

        return view('livewire.backend.prize.manage-prize', [
            'prizes' => $prizes,
        ])->layout('livewire.backend.base');
    }
}

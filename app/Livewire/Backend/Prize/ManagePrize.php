<?php

namespace App\Livewire\Backend\Prize;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Prize;

class ManagePrize extends Component
{
    use WithPagination, WithFileUploads;

    public $name, $amount, $description,$image_path, $is_active = true, $prize_id;
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

            // Process and save the new image
            $imageName = uniqid() . '.' . $this->image_path->getClientOriginalExtension();

            // Use Intervention Image to handle and resize the image

            $img=$this->image_path;
            $manager = new ImageManager(new Driver());
            $img1=$manager->read($img);
            $img1=$img1->resize(300, 300)->toJpeg(80);

            // Save the image to the storage folder
            $filePath = "public/prize/{$imageName}";
            Storage::put($filePath, $img1->__toString());

            $imagePath = $filePath; // Store the relative path
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
        Prize::find($id)?->delete();
        session()->flash('message', 'Prize Deleted Successfully');
    }

    private function resetInputFields()
    {
        $this->prize_id = null;
        $this->name = '';
        $this->amount = '';
        $this->description = '';
        $this->is_active = true;
    }

    public function render()
    {
        // $prizes = Prize::where('name', 'like', '%' . $this->search . '%')
        //     ->paginate(10)
        //     ->withQueryString();
        $prizes = Prize::latest()->get();

        return view('livewire.backend.prize.manage-prize', [
            'prizes' => $prizes,
        ])->layout('livewire.backend.base');
    }
}

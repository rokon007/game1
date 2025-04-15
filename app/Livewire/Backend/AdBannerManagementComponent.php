<?php

namespace App\Livewire\Backend;

use Livewire\Component;
use App\Models\AdBanner;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Livewire\Attributes\On;

class AdBannerManagementComponent extends Component
{
    use WithPagination, WithFileUploads;
    public $images,$photo1,$imageDelet,$titel,$text,$url,$status,$button_name;
    public $photo=[];
    public $titel1=[];
    public $text1=[];
    public $url1=[];
    public $status1=[];
    public $button_name1=[];
    public $delitModel=false;

    public function deleteImage($id)
    {
        $this->delitModel=true;
        $this->imageDelet =AdBanner::find($id);
    }

    public function cancel()
    {
        $this->delitModel=false;
    }

    protected $rules = [
        'titel' => 'required|string|max:255', // 'titel' থেকে 'title' সংশোধন
        'text' => 'required|string|max:255',
        'url' => 'nullable|url', // URL এর জন্য সঠিক ভ্যালিডেশন
        'status' => 'required|boolean', // boolean + required
        'photo1' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024',
        'button_name' => 'nullable|string|max:255',
    ];


    public function mount()
    {
        $this-> refreshComponent();
    }
    public function refreshComponent()
    {
        $this->images=AdBanner::get();
        foreach ($this->images as $image) {
            $this->titel1[$image->id] = $image->title;
            $this->text1[$image->id]=$image->text;
            $this->url1[$image->id]=$image->url;
            $this->status1[$image->id]=$image->is_active;
            $this->button_name1[$image->id]=$image->button_name;

        }
    }

    public function deleteImage1()
    {
        $image = $this->imageDelet;
        if ($image->image_path) {
            Storage::disk('public')->delete($image->image_path);
        }
        $image->delete();
        $this->delitModel=false;
        $this-> refreshComponent();
        session()->flash('delete', 'Image deleted successfully.');
    }

    public function updateMoreImage()
    {
        $this->validate();
        // Handle Image Upload
        $imagePath = null;
        if ($this->photo1) {
            $imageName = uniqid() . '.' . $this->photo1->getClientOriginalExtension();
            $img=$this->photo1;
            $manager = new ImageManager(new Driver());
            $img1=$manager->read($img);
            $img1=$img1->resize(1920, 600)->toJpeg(80);
            $filePath = "public/banner/{$imageName}";
            Storage::put($filePath, $img1->__toString());
            $imagePath = $filePath;
        }

        // Save Image Data
        AdBanner::create([
            'title' => $this->titel,
            'text' => $this->text,
            'image_path' => $imagePath,
            'url' => $this->url,
            'is_active' => $this->status,
            'button_name' => $this->button_name,
        ]);
        $this-> refreshComponent();
        // Reset Inputs
        $this->reset(['photo1', 'titel', 'text','url','status']);
        $this->dispatch("updated1");
    }

    public function updateImage($id)
    {
        $this->validate([
            "titel1.$id" => 'required|string|max:255',
            "text1.$id" => 'nullable|string|max:255',
            "url1.$id" => 'nullable|url',
            "status1.$id" => 'required|boolean',
            "photo1.$id" => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
            "button_name1.$id" => 'nullable|string|max:255',
        ]);

        // Retrieve the image data
        $imageData = AdBanner::find($id);
        $imagePath = $imageData?->image_path;

        // Handle Image Upload
        if (isset($this->photo[$id])) {
                // Delete old image if exists
                if ($imagePath) {
                    Storage::delete($imagePath);
                }

                // Generate a unique name and process the image
                $imageName = uniqid() . '.' . $this->photo[$id]->getClientOriginalExtension();
                $img = $this->photo[$id];

                // Resize and optimize the image
                $manager = new ImageManager(new Driver());
                $img1=$manager->read($img);
                $img1=$img1->resize(1920, 600)->toJpeg(80);

                // Store the image
                $filePath = "public/banner/{$imageName}";
                Storage::put($filePath, $img1->__toString());
                $imagePath = $filePath;
            }



            // Update the database record
                $imageData->update([
                    'title' => $this->titel1[$id],
                    'text' => $this->text1[$id] ?? null,
                    'image_path' => $imagePath,
                    'url' => $this->url1[$id] ?? null,
                    'is_active' => $this->status1[$id],
                    'button_name' => $this->button_name1[$id] ?? null,
                ]);
            $this-> refreshComponent();
            $this->dispatch("updated.{$id}");
    }


    public function render()
    {
        return view('livewire.backend.ad-banner-management-component')->layout('livewire.backend.base');
    }
}


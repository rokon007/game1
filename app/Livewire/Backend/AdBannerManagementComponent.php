<?php

namespace App\Livewire\Backend;

use Livewire\Component;
use App\Models\AdBanner;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;

class AdBannerManagementComponent extends Component
{
    use WithPagination, WithFileUploads;

    public $images, $photo1, $imageDelet, $titel, $text, $url, $status, $button_name;
    public $photo = [];
    public $titel1 = [];
    public $text1 = [];
    public $url1 = [];
    public $status1 = [];
    public $button_name1 = [];
    public $delitModel = false;

    public function deleteImage($id)
    {
        $this->delitModel = true;
        $this->imageDelet = AdBanner::find($id);
    }

    public function cancel()
    {
        $this->delitModel = false;
    }

    protected $rules = [
        'titel' => 'required|string|max:255',
        'text' => 'required|string|max:255',
        'url' => 'nullable|url',
        'status' => 'required|boolean',
        'photo1' => 'required|image|mimes:jpeg,png,jpg,gif',
        'button_name' => 'nullable|string|max:255',
    ];

    public function mount()
    {
        $this->refreshComponent();
    }

    public function refreshComponent()
    {
        $this->images = AdBanner::get();
        foreach ($this->images as $image) {
            $this->titel1[$image->id] = $image->title;
            $this->text1[$image->id] = $image->text;
            $this->url1[$image->id] = $image->url;
            $this->status1[$image->id] = $image->is_active;
            $this->button_name1[$image->id] = $image->button_name;
        }
    }

    public function deleteImage1()
    {
        $image = $this->imageDelet;
        if ($image->image_path) {
            Storage::disk('public')->delete($image->image_path);
        }
        $image->delete();
        $this->delitModel = false;
        $this->refreshComponent();
        session()->flash('delete', 'Image deleted successfully.');
    }

    public function updateMoreImage()
    {
        $this->validate();

        $imagePath = null;
        if ($this->photo1) {
            $imageName = uniqid().'.'.$this->photo1->getClientOriginalExtension();
            $filePath = "banner/{$imageName}";

            // সরাসরি ইমেজ স্টোর করুন (রিসাইজ ছাড়া)
            $this->photo1->storeAs('public', $filePath);
            $imagePath = $filePath;
        }

        AdBanner::create([
            'title' => $this->titel,
            'text' => $this->text,
            'image_path' => $imagePath,
            'url' => $this->url,
            'is_active' => $this->status,
            'button_name' => $this->button_name,
        ]);

        $this->refreshComponent();
        $this->reset(['photo1', 'titel', 'text', 'url', 'status']);
        $this->dispatch("updated1");
    }

    public function updateImage($id)
    {
        $this->validate([
            "titel1.$id" => 'required|string|max:255',
            "text1.$id" => 'nullable|string|max:255',
            "url1.$id" => 'nullable|url',
            "status1.$id" => 'required|boolean',
            "photo.$id" => 'nullable|image|mimes:jpeg,png,jpg,gif',
            "button_name1.$id" => 'nullable|string|max:255',
        ]);

        $imageData = AdBanner::find($id);
        $imagePath = $imageData?->image_path;

        if (isset($this->photo[$id])) {
            if ($imagePath) {
                Storage::delete('public/'.$imagePath);
            }

            $imageName = uniqid().'.'.$this->photo[$id]->getClientOriginalExtension();
            $filePath = "banner/{$imageName}";

            // সরাসরি ইমেজ স্টোর করুন (রিসাইজ ছাড়া)
            $this->photo[$id]->storeAs('public', $filePath);
            $imagePath = $filePath;
        }

        $imageData->update([
            'title' => $this->titel1[$id],
            'text' => $this->text1[$id] ?? null,
            'image_path' => $imagePath,
            'url' => $this->url1[$id] ?? null,
            'is_active' => $this->status1[$id],
            'button_name' => $this->button_name1[$id] ?? null,
        ]);

        $this->refreshComponent();
        $this->dispatch("updated.{$id}");
    }

    public function render()
    {
        return view('livewire.backend.ad-banner-management-component')->layout('livewire.backend.base');
    }
}

<?php

namespace App\Livewire\Backend;

use Livewire\Component;
use App\Models\HowToGuide;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Storage;

class HowToGuideManager extends Component
{
    public $guides, $title, $description, $video_url, $guideId;
    public $isEditMode = false;

    protected $listeners = ['editGuide'];

    public function mount()
    {
        $this->loadGuides();
    }

    public function loadGuides()
    {
        $this->guides = HowToGuide::latest()->get();
    }

    public function resetForm()
    {
        $this->reset(['title', 'description', 'video_url', 'guideId', 'isEditMode']);
        $this->dispatch('resetForm');
        $this->dispatch('resetEditor');
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'video_url' => 'nullable',
        ]);

        // Process images in description
        $description = $this->processDescriptionImages($this->description);

        HowToGuide::create([
            'title' => $this->title,
            'description' => $description,
            'video_url' => $this->video_url,
        ]);

        session()->flash('success', 'Guide created successfully!');
        $this->resetForm();
        $this->loadGuides();
    }

    public function edit($id)
    {
        $guide = HowToGuide::findOrFail($id);
        $this->guideId = $id;
        $this->title = $guide->title;
        $this->description = $guide->description;
        $this->video_url = $guide->video_url;
        $this->isEditMode = true;

        // Dispatch event to update editor with a small delay
        $this->dispatch('updateEditor', content: $guide->description);

        // Alternative: Try dispatching with different method
        $this->js("
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('updateEditor', {
                    detail: { content: " . json_encode($guide->description) . " }
                }));
            }, 100);
        ");
    }

    public function update()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'video_url' => 'nullable',
        ]);

        $guide = HowToGuide::findOrFail($this->guideId);

        // Process images in description
        $description = $this->processDescriptionImages($this->description);

        $guide->update([
            'title' => $this->title,
            'description' => $description,
            'video_url' => $this->video_url,
        ]);

        session()->flash('success', 'Guide updated successfully!');
        $this->resetForm();
        $this->loadGuides();
    }

    public function delete($id)
    {
        $guide = HowToGuide::findOrFail($id);

        // Delete associated images from description
        $this->deleteDescriptionImages($guide->description);

        $guide->delete();
        session()->flash('success', 'Guide deleted successfully!');
        $this->loadGuides();
    }

    protected function processDescriptionImages($description)
    {
        // Create post_image directory if it doesn't exist
        $postImageDir = public_path('uploads/post_image/');
        if (!file_exists($postImageDir)) {
            mkdir($postImageDir, 0777, true);
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($description, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $images = $dom->getElementsByTagName('img');

        foreach ($images as $img) {
            $src = $img->getAttribute('src');

            if (strpos($src, 'uploads/temp/') !== false) {
                $tempFileName = basename($src);
                $tempPath = public_path('uploads/temp/' . $tempFileName);
                $newPath = public_path('uploads/post_image/' . $tempFileName);

                if (file_exists($tempPath)) {
                    // Copy file instead of rename to avoid issues
                    copy($tempPath, $newPath);
                    // Then delete the temp file
                    unlink($tempPath);
                }

                $img->setAttribute('src', asset('uploads/post_image/' . $tempFileName));
            }
        }

        return $dom->saveHTML();
    }

    protected function deleteDescriptionImages($description)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($description, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $images = $dom->getElementsByTagName('img');

        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            $path = parse_url($src, PHP_URL_PATH);
            $filePath = public_path($path);

            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    public function render()
    {
        return view('livewire.backend.how-to-guide-manager')->layout('livewire.backend.base');
    }
}

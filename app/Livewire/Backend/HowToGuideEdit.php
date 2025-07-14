<?php

namespace App\Livewire\Backend;

use Livewire\Component;
use App\Models\HowToGuide;

class HowToGuideManager extends Component
{
    public $guides, $title, $description, $video_url, $guideId, $contentData;
    public $isEditMode = false;

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
        $this->title = '';
        $this->description = '';
        $this->video_url = '';
        $this->guideId = null;
        $this->isEditMode = false;

        // Reset editor content
        $this->dispatch('resetEditor');
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'nullable',
        ]);

        // CKEditor-এর মাধ্যমে আপলোড করা description-এর ইমেজ প্রসেস করুন
        $description = $this->description;

        // `temp` ফোল্ডারে থাকা ইমেজগুলো `blog` ফোল্ডারে কপি করুন
        $dom = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true); // লোডিং এর সময় ইরর আটকাতে
        $dom->loadHTML(mb_convert_encoding($description, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $images = $dom->getElementsByTagName('img');

        // image path store করার জন্য আলাদা array
        $descriptionImages = [];

        foreach ($images as $img) {
            $src = $img->getAttribute('src');

            // কেবলমাত্র `temp` ফোল্ডারের ইমেজগুলো প্রসেস করা হবে
            if (strpos($src, 'uploads/temp/') !== false) {
                $tempFileName = basename($src);
                $tempPath = public_path('uploads/temp/' . $tempFileName);
                $newPath = public_path('uploads/post_image/' . $tempFileName);

                // ফাইল সরানো (move)
                if (file_exists($tempPath)) {
                    rename($tempPath, $newPath);
                }

                // নতুন path সেট করুন
                $img->setAttribute('src', asset('public/uploads/post_image/' . $tempFileName));

                $descriptionImages[] = 'public/uploads/post_image/' . $tempFileName;
            }
        }

        $description = $dom->saveHTML();

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
        $this->contentData = $guide->description;
        $this->video_url = $guide->video_url;
        $this->isEditMode = true;

        // Dispatch event to update editor content
        $this->dispatch('updateEditor', content: $this->description);
    }

    public function update()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'nullable|url',
        ]);

        $guide = HowToGuide::findOrFail($this->guideId);

        $guide->update([
            'title' => $this->title,
            'description' => $this->description,
            'video_url' => $this->video_url,
        ]);

        session()->flash('success', 'Guide updated successfully!');
        $this->resetForm();
        $this->loadGuides();
    }

    public function delete($id)
    {
        HowToGuide::findOrFail($id)->delete();
        session()->flash('success', 'Guide deleted successfully!');
        $this->loadGuides();
    }

    public function render()
    {
        return view('livewire.backend.how-to-guide-manager')->layout('livewire.backend.base');
    }
}

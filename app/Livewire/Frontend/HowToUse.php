<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use App\Models\HowToGuide;

class HowToUse extends Component
{
    public $title, $description, $video_url;
    public $dataMode=true;
    public $detailsMode=false;

    public $data = [];

    public function mount()
    {
         $this->data = HowToGuide::all();
    }

    public function details($id)
    {
        $guide=HowToGuide::find($id);
        $this->title=$guide->title;
        $this->description=$guide->description;
        $this->video_url=$guide->video_url;
        $this->dataMode=false;
        $this->detailsMode=true;
    }

    public function backToList()
    {
        dd('ok');
        $this->dataMode = true;
        $this->detailsMode = false;
        $this->reset(['title', 'description', 'video_url']);
    }

    public function render()
    {
        return view('livewire.frontend.how-to-use')->layout('livewire.layout.frontend.base');
    }
}

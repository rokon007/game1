<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class LocationUpdater extends Component
{
    public $latitude;
    public $longitude;

    public function mount()
    {
        $this->latitude = auth()->user()->latitude;
        $this->longitude = auth()->user()->longitude;
    }

    public function updateLocation($lat, $lng)
    {
        // $this->latitude = $lat;
        // $this->longitude = $lng;

        $this->latitude = $lat;
        $this->longitude = $lng;

        auth()->user()->update([
            'latitude' => 20,
            'longitude' => 25,
        ]);
    }

    public function render()
    {
        return view('livewire.location-updater');
    }
}


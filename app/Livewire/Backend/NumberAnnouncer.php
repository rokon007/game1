<?php

namespace App\Livewire\Backend;

use App\Models\Announcement;
use App\Models\Game;
use App\Events\NumberAnnounced;
use Illuminate\Support\Facades\Broadcast;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NumberAnnouncer extends Component
{
    public $gameId;
    public $calledNumbers = [];
    public $nextNumber;
    public $selectedNumber;
    public $game;

    public function mount($gameId)
    {
        $this->gameId = $gameId;
        $this->game = Game::find($gameId);
        // Load already called numbers
        $this->calledNumbers = Announcement::where('game_id', $gameId)->pluck('number')->toArray();
    }

    public function announceNumber()
    {
        $number = $this->selectedNumber;

        if (!$number) {
            session()->flash('error', 'Please select a number first.');
            return;
        }

        // চেক করো এই নাম্বার আগে ঘোষণা করা হয়েছে কি না
        $exists = Announcement::where('game_id', $this->gameId)
            ->where('number', $number)
            ->exists();

        if ($exists) {
            session()->flash('error', 'This number has already been announced.');
            return;
        }

        try {
            // ঘোষণা তৈরি করো
            Announcement::create([
                'game_id' => $this->gameId,
                'number' => $number,
            ]);

            // Add to called numbers array
            if (!in_array($number, $this->calledNumbers)) {
                $this->calledNumbers[] = $number;
            }

            // Clear selected number
            $this->selectedNumber = null;

            // Broadcast the event
            broadcast(new NumberAnnounced($this->gameId, $number))->toOthers();

            // Also dispatch for the current user using Livewire 3 syntax
            $this->dispatch('numberAnnounced', number: $number);

            // Log success
            Log::info("Number $number announced successfully for game {$this->gameId}");

            session()->flash('success', "Number $number has been announced successfully!");
        } catch (\Exception $e) {
            Log::error("Error announcing number: " . $e->getMessage());
            session()->flash('error', "Error announcing number: " . $e->getMessage());
        }
    }

    public function callNextNumber()
    {
        $available = collect(range(1, 90))->diff($this->calledNumbers)->values();

        if ($available->isEmpty()) {
            session()->flash('error', 'All numbers have been announced.');
            return;
        }

        $this->nextNumber = $available->random();

        // Set as selected number
        $this->selectedNumber = $this->nextNumber;

        // Announce it
        $this->announceNumber();
    }

    public function render()
    {
        return view('livewire.backend.number-announcer')->layout('livewire.backend.base');
    }
}

<?php

namespace App\Livewire\Backend;

use App\Models\Announcement;
use App\Models\Game;
use Illuminate\Support\Facades\Broadcast;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
// use Livewire\Attributes\On;


class NumberAnnouncer extends Component
{
    public $gameId;
    public $calledNumbers = [];
    public $nextNumber;
    public $selectedNumber;
    public $game;

        // #[On('number-announced')]
        public function announceNumber()
        {
            $number=$this->selectedNumber;

            // চেক করো এই নাম্বার আগে ঘোষণা করা হয়েছে কি না
            $exists = Announcement::where('game_id', $this->gameId)
                ->where('number', $number)
                ->exists();

            if ($exists) {
                session()->flash('error', 'This number has already been announced.');
                return;
            }

            // ঘোষণা তৈরি করো
            Announcement::create([
                'game_id' => $this->gameId,
                'number' => $number,
            ]);
            event(new \App\Events\NumberAnnounced($this->gameId, $this->selectedNumber));
        }

        public function mount($gameId)
        {
            $this->gameId=$gameId;
            $this->game = Game::find($gameId);
        }

        // public function mount($gameId)
        // {
        //     $this->gameId = $gameId;
        //     $this->calledNumbers = Announcement::where('game_id', $gameId)->pluck('number')->toArray();
        // }

        public function callNextNumber()
        {
            $available = collect(range(1, 90))->diff($this->calledNumbers)->values();

            if ($available->isEmpty()) {
                session()->flash('error', 'All numbers have been announced.');
                return;
            }

            $this->nextNumber = $available->random();
            $this->calledNumbers[] = $this->nextNumber;

            Announcement::create([
                'game_id' => $this->gameId,
                'number' => $this->nextNumber,
            ]);

            // event(new \App\Events\NumberAnnounced($this->gameId, $this->nextNumber));

            // broadcast(new \App\Events\NumberAnnounced($this->gameId, $this->nextNumber))->toOthers();
        }


        public function render()
        {

            return view('livewire.backend.number-announcer')->layout('livewire.backend.base');

        }
    }

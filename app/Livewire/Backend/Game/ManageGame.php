<?php

namespace App\Livewire\Backend\Game;

use Livewire\Component;
use App\Models\Game;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class ManageGame extends Component
{
    use WithPagination, WithFileUploads;

    public $title, $scheduled_at, $ticket_price, $is_active = true, $game_id;
    public $search = '';

    public function edit($id)
    {
        $game = Game::findOrFail($id);
        $this->game_id = $game->id;
        $this->title = $game->title;
        $this->scheduled_at = $game->scheduled_at;
        $this->ticket_price = $game->ticket_price;
        $this->is_active = $game->is_active ? 1 : 0;
    }

    public function delete($id)
    {
        $game = Game::find($id);
        if ($game) {
            $game->delete();
            session()->flash('message', 'Prize Deleted Successfully');
        }
    }

    private function resetInputFields()
    {
        $this->reset(['title', 'scheduled_at', 'ticket_price']);
        $this->is_active = true;
    }

    public function store()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'scheduled_at' => 'required|date',
            'ticket_price' => 'required|numeric|min:0',
        ]);

        // Game::create([
        //     'title' => $this->title,
        //     'scheduled_at' => $this->scheduled_at,
        //     'ticket_price' => $this->ticket_price,
        //     'is_active' => $this->is_active,
        // ]);

        Game::updateOrCreate(
            ['id' => $this->game_id],
            [
                'title' => $this->title,
                'scheduled_at' => $this->scheduled_at,
                'ticket_price' => $this->ticket_price,
                'is_active' => $this->is_active,
            ]
        );

        $this->game_id=false;
        session()->flash('message', $this->game_id ? 'Game Updated Successfully' : 'Game Created Successfully');
        $this->reset(['title', 'scheduled_at', 'ticket_price', 'is_active']);
    }

    public function render()
    {
        $games = Game::when($this->search, function($query) {
            return $query->where('title', 'like', '%'.$this->search.'%');
        })
        ->latest()
        ->get();
        return view('livewire.backend.game.manage-game', [
            'games' => $games,
        ])->layout('livewire.backend.base');
    }
}

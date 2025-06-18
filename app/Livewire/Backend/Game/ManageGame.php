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
    public $corner_prize;
    public $top_line_prize;
    public $middle_line_prize;
    public $bottom_line_prize;
    public $full_house_prize;
    public $search = '';

    public function edit($id)
    {
        $game = Game::findOrFail($id);
        $this->game_id = $game->id;
        $this->title = $game->title;
        $this->scheduled_at = \Carbon\Carbon::parse($game->scheduled_at)->format('Y-m-d\TH:i');
        $this->ticket_price = $game->ticket_price;
        $this->is_active = $game->is_active ? 1 : 0;

        $this->corner_prize = $game->corner_prize;
        $this->top_line_prize = $game->top_line_prize;
        $this->middle_line_prize = $game->middle_line_prize;
        $this->bottom_line_prize = $game->bottom_line_prize;
        $this->full_house_prize = $game->full_house_prize;
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

            'corner_prize' => 'required|numeric|min:0',
            'top_line_prize' => 'required|numeric|min:0',
            'middle_line_prize' => 'required|numeric|min:0',
            'bottom_line_prize' => 'required|numeric|min:0',
            'full_house_prize' => 'required|numeric|min:0',
        ]);


        Game::updateOrCreate(
            ['id' => $this->game_id],
            [
                'title' => $this->title,
                'scheduled_at' => $this->scheduled_at,
                'ticket_price' => $this->ticket_price,
                'is_active' => $this->is_active,

                'corner_prize' => $this->corner_prize,
                'top_line_prize' => $this->top_line_prize,
                'middle_line_prize' => $this->middle_line_prize,
                'bottom_line_prize' => $this->bottom_line_prize,
                'full_house_prize' => $this->full_house_prize,
            ]
        );

        $this->game_id=false;
        session()->flash('message', $this->game_id ? 'Game Updated Successfully' : 'Game Created Successfully');
        $this->reset(['title', 'scheduled_at', 'ticket_price', 'is_active', 'corner_prize', 'top_line_prize', 'middle_line_prize', 'bottom_line_prize', 'full_house_prize']);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $games = Game::when($this->search, function($query) {
            return $query->where('title', 'like', '%'.$this->search.'%');
        })
        ->latest()
        ->paginate(18);
        return view('livewire.backend.game.manage-game', [
            'games' => $games,
        ])->layout('livewire.backend.base');
    }
}

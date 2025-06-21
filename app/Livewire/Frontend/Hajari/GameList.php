<?php

namespace App\Livewire\Frontend\Hajari;

use App\Models\HajariGame;
use App\Models\HajariGameInvitation;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;


class GameList extends Component
{
    use WithPagination;

    public $filter = 'all'; // all, my_games, invitations, available

    public function joinGame(HajariGame $game)
    {
        if (!$game->canJoin(Auth::user())) {
            // session()->flash('error', 'Cannot join this game.');
            $this->dispatch('showToast', 'Cannot join this game.', 'error');
            return;
        }

        if (Auth::user()->credit < $game->bid_amount) {
            //session()->flash('error', 'Insufficient balance to join this game.');
            $this->dispatch('showToast', 'Insufficient balance to join this game', 'error');
            return;
        }
//$this->dispatch('showToast', 'User created successfully!', 'success');
        $position = $game->participants()->count() + 1;

        $game->participants()->create([
            'user_id' => Auth::id(),
            'status' => 'joined',
            'position' => $position
        ]);

        // session()->flash('success', 'Successfully joined the game!');
        $this->dispatch('showToast', 'Successfully joined the game!', 'success');
        return redirect()->route('games.show', $game);
    }

    public function requestToJoin(HajariGame $game)
    {
        // Create invitation request
        HajariGameInvitation::create([
            'hajari_game_id' => $game->id,
            'inviter_id' => Auth::id(), // User requesting to join
            'invitee_id' => $game->creator_id, // Game creator
            'message' => 'Requesting to join your game: ' . $game->title,
            'expires_at' => now()->addHours(24)
        ]);

        // session()->flash('success', 'Join request sent to game creator!');
         $this->dispatch('showToast', 'Join request sent to game creator!', 'success');
    }

    public function render()
    {
        $query = HajariGame::with(['creator', 'participants.user']);

        switch ($this->filter) {
            case 'my_games':
                $query->where('creator_id', Auth::id());
                break;
            case 'invitations':
                $query->whereHas('invitations', function($q) {
                    $q->where('invitee_id', Auth::id())
                      ->where('status', 'pending');
                });
                break;
            case 'available':
                $query->where('status', 'pending')
                      ->where('creator_id', '!=', Auth::id())
                      ->whereDoesntHave('participants', function($q) {
                          $q->where('user_id', Auth::id());
                      });
                break;
        }

        $games = $query->latest()->paginate(10);

        //return view('livewire.game-list', compact('games'));
        return view('livewire.frontend.hajari.game-list', compact('games'))->layout('livewire.layout.frontend.base');
    }
}




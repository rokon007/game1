<?php

namespace App\Livewire\Frontend\Hajari;

use App\Models\HajariGame;
use App\Models\User;
use App\Models\HajariGameInvitation;
use App\Notifications\GameInvitationNotification;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class GameCreate extends Component
{
    public $title = '';
    public $description = '';
    public $bid_amount = 10;
    public $scheduled_at = '';
    public $invited_users = [];
    public $search_users = '';
    public $available_users = [];

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'bid_amount' => 'required|numeric|min:1|max:10000',
        'scheduled_at' => 'required|date|after:now',
        'invited_users' => 'array|max:3'
    ];

    public function mount()
    {
        $this->scheduled_at = now()->addHour()->format('Y-m-d\TH:i');
    }

    public function updatedSearchUsers()
    {
        if (strlen($this->search_users) >= 2) {
            $this->available_users = User::where('id', '!=', Auth::id())
                ->where(function($query) {
                    $query->where('name', 'like', '%' . $this->search_users . '%')
                          ->orWhere('email', 'like', '%' . $this->search_users . '%');
                })
                ->limit(10)
                ->get()
                ->toArray();
        } else {
            $this->available_users = [];
        }
    }

    public function addUser($userId)
    {
        if (!in_array($userId, $this->invited_users) && count($this->invited_users) < 3) {
            $this->invited_users[] = $userId;
        }
        $this->search_users = '';
        $this->available_users = [];
    }

    public function removeUser($userId)
    {
        $this->invited_users = array_filter($this->invited_users, fn($id) => $id != $userId);
    }

    public function createGame()
    {

        //$this->validate();

        // Check user credit
        if (Auth::user()->credit < $this->bid_amount) {
            session()->flash('error', 'Insufficient balance to create this game.');
            return;
        }

        $game = HajariGame::create([
            'creator_id' => Auth::id(),
            'title' => $this->title,
            'description' => $this->description,
            'bid_amount' => $this->bid_amount,
            'scheduled_at' => $this->scheduled_at,
            'status' => 'pending'
        ]);

        // Add creator as participant
        $game->participants()->create([
            'user_id' => Auth::id(),
            'status' => 'joined',
            'position' => 1
        ]);

        // Send invitations
        foreach ($this->invited_users as $userId) {
            $invitation = HajariGameInvitation::create([
                'hajari_game_id' => $game->id,
                'inviter_id' => Auth::id(),
                'invitee_id' => $userId,
                'expires_at' => now()->addDays(1)
            ]);

            $user = User::find($userId);
            $user->notify(new GameInvitationNotification($invitation));
        }

        session()->flash('success', 'Game created successfully!');
        return redirect()->route('games.show', $game);
    }

    public function render()
    {
         return view('livewire.frontend.hajari.game-create')->layout('livewire.layout.frontend.base');
    }
}



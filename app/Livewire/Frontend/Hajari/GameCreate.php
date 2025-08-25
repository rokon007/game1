<?php

namespace App\Livewire\Frontend\Hajari;

use App\Models\HajariGame;
use App\Models\User;
use App\Models\HajariGameInvitation;
use App\Models\Transaction;
use App\Notifications\GameInvitationNotification;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;

class GameCreate extends Component
{
    public $title = '';
    public $description = '';
    public $bid_amount = 10;
    public $scheduled_at = '';
    public $invited_users = [];
    public $search_users = '';
    public $available_users = [];
    public $showConfirmationModal = false;

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

    public function confirmCreate() // Add this method
    {
        $this->validate();

        // Check user credit
        if (Auth::user()->credit < $this->bid_amount) {
            session()->flash('error', 'Insufficient balance to create this game.');
            return;
        }

        $this->showConfirmationModal = true;
    }

    // public function createGame()
    // {

    //     //$this->validate();

    //     // Check user credit
    //     if (Auth::user()->credit < $this->bid_amount) {
    //         session()->flash('error', 'Insufficient balance to create this game.');
    //         return;
    //     }

    //     $game = HajariGame::create([
    //         'creator_id' => Auth::id(),
    //         'title' => $this->title,
    //         'description' => $this->description,
    //         'bid_amount' => $this->bid_amount,
    //         'scheduled_at' => $this->scheduled_at,
    //         'status' => 'pending'
    //     ]);

    //     // Add creator as participant
    //     $game->participants()->create([
    //         'user_id' => Auth::id(),
    //         'status' => 'joined',
    //         'position' => 1
    //     ]);

    //     // Send invitations
    //     foreach ($this->invited_users as $userId) {
    //         $invitation = HajariGameInvitation::create([
    //             'hajari_game_id' => $game->id,
    //             'inviter_id' => Auth::id(),
    //             'invitee_id' => $userId,
    //             'expires_at' => now()->addDays(1)
    //         ]);

    //         $user = User::find($userId);
    //         $user->notify(new GameInvitationNotification($invitation));
    //     }

    //     session()->flash('success', 'Game created successfully!');
    //     return redirect()->route('games.show', $game);
    // }

    // public function createGame()
    // {
    //     // Use database transaction for data consistency
    //     DB::transaction(function () {
    //         $user = Auth::user();
    //         $admin = User::find(1); // Assuming admin user ID is 1

    //         // Deduct bid amount from creator
    //         $user->credit -= $this->bid_amount;
    //         $user->save();

    //         // Add bid amount to admin account
    //         $admin->credit += $this->bid_amount;
    //         $admin->save();

    //         // Create transaction for creator (debit)
    //         Transaction::create([
    //             'user_id' => $user->id,
    //             'type' => 'debit',
    //             'amount' => $this->bid_amount,
    //             'details' => 'Game creation bid: ' . $this->title,
    //         ]);

    //         // Create transaction for admin (credit)
    //         Transaction::create([
    //             'user_id' => $admin->id,
    //             'type' => 'credit',
    //             'amount' => $this->bid_amount,
    //             'details' => 'Game creation bid from user: ' . $user->name . ' for game: ' . $this->title,
    //         ]);

    //         $game = HajariGame::create([
    //             'creator_id' => $user->id,
    //             'title' => $this->title,
    //             'description' => $this->description,
    //             'bid_amount' => $this->bid_amount,
    //             'scheduled_at' => $this->scheduled_at,
    //             'status' => 'pending'
    //         ]);

    //         // Add creator as participant
    //         $game->participants()->create([
    //             'user_id' => $user->id,
    //             'status' => 'joined',
    //             'position' => 1
    //         ]);

    //         // Send invitations
    //         foreach ($this->invited_users as $userId) {
    //             $invitation = HajariGameInvitation::create([
    //                 'hajari_game_id' => $game->id,
    //                 'inviter_id' => $user->id,
    //                 'invitee_id' => $userId,
    //                 'expires_at' => now()->addDays(1)
    //             ]);

    //             // Load relationships for notification
    //             $invitation->load(['hajarigame', 'inviter']);

    //             $invitedUser = User::find($userId);
    //             $invitedUser->notify(new GameInvitationNotification($invitation));
    //         }
    //     });

    //     $this->showConfirmationModal = false;
    //     session()->flash('success', 'Game created successfully!');
    //     return redirect()->route('games.show', $game);
    // }

    public function createGame()
    {
        $game = null; // Declare $game outside the closure

        // Use database transaction for data consistency
        DB::transaction(function () use (&$game) { // Use pass by reference
            $user = Auth::user();
            $admin = User::find(1); // Assuming admin user ID is 1

            // Deduct bid amount from creator
            $user->credit -= $this->bid_amount;
            $user->save();

            // Add bid amount to admin account
            $admin->credit += $this->bid_amount;
            $admin->save();

            // Create transaction for creator (debit)
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $this->bid_amount,
                'details' => 'Game creation : ' . $this->title,
            ]);

            // Create transaction for admin (credit)
            Transaction::create([
                'user_id' => $admin->id,
                'type' => 'credit',
                'amount' => $this->bid_amount,
                'details' => 'Game creation bid from user: ' . $user->name . ' for game: ' . $this->title,
            ]);

            $game = HajariGame::create([
                'creator_id' => $user->id,
                'title' => $this->title,
                'description' => $this->description,
                'bid_amount' => $this->bid_amount,
                'scheduled_at' => $this->scheduled_at,
                'status' => 'pending'
            ]);

            // Add creator as participant
            $game->participants()->create([
                'user_id' => $user->id,
                'status' => 'joined',
                'position' => 1
            ]);

            // Send invitations
            foreach ($this->invited_users as $userId) {
                $invitation = HajariGameInvitation::create([
                    'hajari_game_id' => $game->id,
                    'inviter_id' => $user->id,
                    'invitee_id' => $userId,
                    'expires_at' => now()->addDays(1)
                ]);

                // Load relationships for notification
                $invitation->load(['hajarigame', 'inviter']);

                $invitedUser = User::find($userId);
                $invitedUser->notify(new GameInvitationNotification($invitation));
            }
        });

        $this->showConfirmationModal = false;
        session()->flash('success', 'Game created successfully!');
        return redirect()->route('games.show', $game);
    }

    public function render()
    {
         return view('livewire.frontend.hajari.game-create')->layout('livewire.layout.frontend.base');
    }
}



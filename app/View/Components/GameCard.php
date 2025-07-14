<?php

namespace App\View\Components;

use App\Models\HajariGame;
use App\Models\User;
use Illuminate\View\Component;

class GameCard extends Component
{
    public HajariGame $game;
    public User $currentUser;

    public function __construct(HajariGame $game, User $currentUser)
    {
        $this->game = $game;
        $this->currentUser = $currentUser;
    }

    public function canJoin(): bool
    {
        return $this->game->canJoin($this->currentUser);
    }

    public function isParticipant(): bool
    {
        return $this->game->isParticipant($this->currentUser);
    }

    public function isCreator(): bool
    {
        return $this->game->creator_id === $this->currentUser->id;
    }

    public function getStatusBadge(): array
    {
        return match($this->game->status) {
            'pending' => ['class' => 'badge rounded-pill badge-warning', 'text' => 'Waiting for Players'],
            'playing' => ['class' => 'badge rounded-pill badge-success', 'text' => 'In Progress'],
            'completed' => ['class' => 'badge rounded-pill badge-secondary', 'text' => 'Completed'],
            'cancelled' => ['class' => 'badge rounded-pill badge-danger', 'text' => 'Cancelled'],
            default => ['class' => 'bg-gray-100 text-gray-800', 'text' => 'Unknown']
        };
    }

    public function getParticipantsCount(): int
    {
        return $this->game->participants()->count();
    }

    public function render()
    {
        return view('components.game-card');
    }
}

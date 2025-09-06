<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HajariGame;

class CancelOldHajariGames extends Command
{
    protected $signature = 'hajari-games:cancel-old';
    protected $description = 'Cancel Hajari games that are older than 36 hours and still active';

    public function handle()
    {
        $games = HajariGame::needCancellation()->get();

        foreach ($games as $game) {
            $game->update(['status' => 'cancelled']);
            $this->info("Cancelled game: {$game->title} (ID: {$game->id})");

            // Optional: Notify participants about the cancellation
            // $this->notifyParticipants($game);
        }

        $this->info("Cancelled {$games->count()} games.");
        return 0;
    }

    // Optional method to notify participants
    protected function notifyParticipants($game)
    {
        // Implement your notification logic here
        // This could be emails, push notifications, etc.
    }
}

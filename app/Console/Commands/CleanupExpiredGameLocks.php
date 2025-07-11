<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupExpiredGameLocks extends Command
{
    protected $signature = 'game-locks:cleanup';
    protected $description = 'Delete expired game locks from the database';

    public function handle()
    {
        $deleted = DB::table('game_locks')
            ->where('expires_at', '<', now())
            ->delete();

        $this->info("Deleted {$deleted} expired locks.");
    }
}

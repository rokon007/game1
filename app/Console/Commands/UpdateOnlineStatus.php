<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class UpdateOnlineStatus extends Command
{
    protected $signature = 'users:update-online-status';
    protected $description = 'Update user online status based on last seen time';

    public function handle()
    {
        $timeout = Config::get('session.lifetime') * 60; // Session lifetime in seconds
        $threshold = now()->subSeconds($timeout);

        User::where('is_online', true)
            ->where('last_seen_at', '<', $threshold)
            ->update(['is_online' => false]);

        $this->info('User online statuses updated successfully.');
    }
}

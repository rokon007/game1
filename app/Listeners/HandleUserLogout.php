<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleUserLogout
{
    public function handle(Logout $event)
    {
        if ($event->user) {
            $event->user->update([
                'is_online' => false,
            ]);
        }
    }
}

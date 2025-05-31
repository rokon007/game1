<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Models\User;

class UserStatusServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Event::listen(Login::class, function ($event) {
            $this->updateOnlineStatus($event->user, true);
        });

        Event::listen(Logout::class, function ($event) {
            $this->updateOnlineStatus($event->user, false);
        });
    }

    protected function updateOnlineStatus($user, $isOnline)
    {
        if ($user instanceof User) {
            $user->update([
                'is_online' => $isOnline,
                'last_seen' => $isOnline ? null : now()
            ]);
        }
    }
}

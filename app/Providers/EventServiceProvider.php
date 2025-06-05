<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Models\User;
use App\Listeners\HandleUserLogout;

class EventServiceProvider extends ServiceProvider
{

    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
         Logout::class => [
            HandleUserLogout::class,
        ],
    ];



    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Event::listen(Login::class, function ($event) {
            $event->user->update(['is_online' => true]);
        });

        // Event::listen(Logout::class, function ($event) {
        //     $event->user->update(['is_online' => false]);
        // });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

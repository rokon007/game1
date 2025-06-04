<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{receiver}', function ($user, $receiver) {
    return (int) $user->id === (int) $receiver;
});

// Broadcast::channel('game.{gameId}', function ($user, $gameId) {
//     return true; // সকল ইউজারের জন্য অ্যাক্সেস
// });

// Broadcast::channel('win.{gameId}', function ($user, $gameId) {
//     return true; // সকল ইউজারের জন্য অ্যাক্সেস
// });

// Broadcast::channel('online-users', function ($user) {
//     return [
//         'id' => $user->id,
//         'name' => $user->name,
//         'avatar' => $user->avatar,
//         'is_online' => $user->is_online
//     ];
// });

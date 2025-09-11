<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SingleSession
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $currentId = session()->getId();

            $sessions = DB::table('sessions')
                ->where('user_id', Auth::id())
                ->pluck('id');

            if ($sessions->count() > 1) {
                DB::table('sessions')
                    ->where('user_id', Auth::id())
                    ->where('id', '!=', $currentId)
                    ->delete();
            }
        }

        return $next($request);
    }
}

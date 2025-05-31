<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateUserLastSeen
{
    public function handle(Request $request, Closure $next)
    {
         if (Auth::check()) {
            Auth::user()->update([
                'last_seen_at' => now(),
                'is_online' => true
            ]);
        }

        return $next($request);
    }
}

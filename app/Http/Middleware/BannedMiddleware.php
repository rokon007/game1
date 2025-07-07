<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class BannedMiddleware
{
    /**
     * Handle an incoming request.  status
     */
    public function handle(Request $request, Closure $next)
    {
        // যদি ইউজার লগিন করা থাকে এবং 'status' ফিল্ডে 'Banned' থাকে
        if (Auth::check() && Auth::user()->status === 'banned') {
            // ব্লকড পেজে রিডিরেক্ট করুন
            return redirect()->route('banned')->with('error', 'Your account has been blocked.');
        }

        // অন্যথায়, রিকোয়েস্ট প্রসেস হতে দিন
        return $next($request);
    }
}

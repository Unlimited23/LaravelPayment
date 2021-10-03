<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Unsubscribed
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->hasActiveSubscription()) {
            return redirect()->route('home');
        }
        return $next($request);
    }
}

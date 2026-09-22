<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApplicantIsNotBlacklisted
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isBlacklisted()) {
            return redirect()->route('dashboard')->with('status', 'Your account is blacklisted and cannot create or edit applications.');
        }

        return $next($request);
    }
}

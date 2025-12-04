<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        $user = auth()->user();
        
        if ($user->role === 0) {
            return $next($request);
        }

        \Log::warning('Unauthorized user access attempt', [
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'url' => $request->fullUrl()
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Forbidden. User access required.'], 403);
        }

        abort(403, 'Unauthorized action.');
    }
}
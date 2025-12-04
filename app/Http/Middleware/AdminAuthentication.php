<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuthentication
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
        
        // Allow admin (role 1) and super admin (role 9)
        if ($user->role === 1 || $user->role === 9) {
            return $next($request);
        }

        \Log::warning('Unauthorized admin access attempt', [
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'url' => $request->fullUrl()
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Forbidden. Admin access required.'], 403);
        }

        abort(403, 'Unauthorized action.');
    }
}
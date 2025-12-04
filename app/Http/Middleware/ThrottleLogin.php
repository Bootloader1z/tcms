<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);
        $maxAttempts = config('auth.login_rate_limit', 5);
        $decayMinutes = 1;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            \Log::warning('Too many login attempts', [
                'ip' => $request->ip(),
                'username' => $request->input('username'),
                'retry_after' => $seconds
            ]);

            return redirect()->back()
                ->withInput($request->only('username'))
                ->with('error', "Too many login attempts. Please try again in {$seconds} seconds.");
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        // Clear rate limiter on successful login
        if (auth()->check()) {
            RateLimiter::clear($key);
        }

        return $response;
    }

    /**
     * Resolve request signature.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        return 'login:' . $request->ip() . ':' . $request->input('username');
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyCsrfForApi
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Get CSRF token from header
        $token = $request->header('X-CSRF-TOKEN');

        // If no token in header, check query parameter
        if (!$token) {
            $token = $request->query('_token');
        }

        // Verify token matches session token
        if (!$token || !hash_equals($request->session()->token(), $token)) {
            return response()->json(['message' => 'CSRF token mismatch'], 419);
        }

        return $next($request);
    }
}

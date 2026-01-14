<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCspHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Set CSP header that allows Livewire and Google Fonts
        $response->headers->set(
            'Content-Security-Policy',
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' data: https: https://fonts.gstatic.com; connect-src 'self' https:;"
        );

        return $response;
    }
}

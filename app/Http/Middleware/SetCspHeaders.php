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
            "default-src 'self' 'unsafe-inline' 'unsafe-eval' data: blob: https:; connect-src 'self' https: blob:; img-src 'self' data: https: blob:; font-src 'self' data: https: https://fonts.gstatic.com;"
        );

        return $response;
    }
}

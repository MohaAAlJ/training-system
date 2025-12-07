<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLocaleFromSession
{
    public function handle($request, Closure $next)
    {
        $locale = session('locale', config('app.locale'));
        \Log::info('Session locale:', ['locale' => $locale]);
        App::setLocale($locale);
        return $next($request);
    }
}

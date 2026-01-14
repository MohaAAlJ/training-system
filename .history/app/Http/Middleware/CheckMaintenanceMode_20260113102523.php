<?php

namespace App\Http\Middleware;

use Closure;
use App\Settings\TrainingSettings;
use Illuminate\Http\Request;
use App\Models\User;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        /** @var TrainingSettings $settings */
        $settings = app(TrainingSettings::class);

        // 1. Skip if maintenance is completely OFF
        if (! $settings->is_maintenance_mode) {
            return $next($request);
        }

        // 2. Technical Loop Prevention: Always allow access to maintenance page, logout, and assets
        if (
            $request->routeIs('filament.home.pages.maintenance') ||
            $request->routeIs('filament.home.auth.logout') ||
            $request->is('livewire/*') ||
            $request->is('filament/assets/*')
        ) {
            return $next($request);
        }

        // 3. Role-based Blocking: IF user is logged in AND their role is in the restricted list -> Block.
        if (auth()->check()) {
            if (in_array(auth()->user()->role, $settings->maintenance_roles ?? [])) {
                return redirect()->route('filament.home.pages.maintenance');
            }
        }

        return $next($request);
    }
}

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
        $settings = app(TrainingSettings::class);

        // 1. Skip if maintenance is OFF
        if (!$settings->is_maintenance_mode) {
            return $next($request);
        }

        // 2. Skip for public application forms
        if ($request->is('WelcomeForm*')) {
            return $next($request);
        }

        // 3. Skip for Admins
        if (auth()->check() && auth()->user()->isAdmin()) {
            return $next($request);
        }

        // 4. Check if user role is restricted
        if (auth()->check()) {
            $user = auth()->user();
            // maintenance_roles is an array of role IDs (integers)
            if (in_array((int) $user->role, $settings->maintenance_roles ?? [])) {
                // Use abort(503) to completely bypass Filament's layout system
                // This ensures ONLY the 503.blade.php page is shown with no dashboard
                abort(503, $settings->maintenance_message);
            }
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use App\Settings\TrainingSettings;
use App\Constants\MaintenanceConstants;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/**
 * Check Maintenance Mode Middleware
 * 
 * This middleware enforces role-based maintenance mode restrictions.
 * When maintenance mode is enabled, users with specific roles are redirected
 * to a maintenance page while admins and public forms remain accessible.
 * 
 * Features:
 * - Role-based access control during maintenance
 * - Admin bypass
 * - Public form exclusion
 * - Prevents redirect loops
 * - Excludes Livewire and asset requests
 * 
 * @package App\Http\Middleware
 */
class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        // Safely retrieve settings, fallback if unavailable
        try {
            $settings = app(TrainingSettings::class);
        } catch (\Exception $e) {
            // If settings are unavailable, allow request to proceed
            \Log::warning('TrainingSettings unavailable in CheckMaintenanceMode middleware', [
                'exception' => $e->getMessage()
            ]);
            return $next($request);
        }

        // 1. Skip if maintenance mode is disabled
        if (!$settings->is_maintenance_mode) {
            return $next($request);
        }

        // 2. Skip if accessing public application forms
        if ($this->isPublicFormRequest($request)) {
            return $next($request);
        }

        // 3. Skip for Admin users (admins always have access)
        if ($this->isAdminUser()) {
            return $next($request);
        }

        // 4. Skip if already on maintenance page or accessing excluded paths
        // This prevents infinite redirect loops
        if ($this->isExcludedRequest($request)) {
            return $next($request);
        }

        // 5. Check if the authenticated user's role is restricted
        if ($this->isRestrictedUser($settings)) {
            return redirect()->route('filament.Home.pages.maintenance');
        }

        return $next($request);
    }

    /**
     * Check if the request is for a public form
     * 
     * @param Request $request
     * @return bool
     */
    protected function isPublicFormRequest(Request $request): bool
    {
        return $request->is(MaintenanceConstants::PUBLIC_FORM_PATH);
    }

    /**
     * Check if the current user is an admin
     * 
     * @return bool
     */
    protected function isAdminUser(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Check if the request should be excluded from maintenance checks
     * 
     * This includes:
     * - Maintenance page itself
     * - Logout routes
     * - Livewire update requests
     * - Filament asset requests
     * 
     * @param Request $request
     * @return bool
     */
    protected function isExcludedRequest(Request $request): bool
    {
        // Check maintenance page path
        if ($request->is(MaintenanceConstants::MAINTENANCE_PAGE_PATH)) {
            return true;
        }

        // Check logout path
        if ($request->is(MaintenanceConstants::LOGOUT_PATH)) {
            return true;
        }

        // Check logout route
        if ($request->routeIs(MaintenanceConstants::LOGOUT_ROUTE_PATTERN)) {
            return true;
        }

        // Check Livewire requests
        if ($request->is(MaintenanceConstants::LIVEWIRE_PATH)) {
            return true;
        }

        // Check Filament asset requests
        if ($request->is(MaintenanceConstants::FILAMENT_ASSETS_PATH)) {
            return true;
        }

        return false;
    }

    /**
     * Check if the authenticated user's role is in the restricted list
     * 
     * @param TrainingSettings $settings
     * @return bool
     */
    protected function isRestrictedUser(TrainingSettings $settings): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();
        $restrictedRoles = $settings->maintenance_roles ?? [];

        return in_array((int) $user->role, $restrictedRoles, true);
    }
}

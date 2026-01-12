<?php

namespace App\Constants;

/**
 * Maintenance Mode Constants
 * 
 * Centralizes all maintenance-related constants for better maintainability.
 * 
 * @package App\Constants
 */
class MaintenanceConstants
{
    /**
     * Route path patterns for maintenance mode exclusions
     */
    public const MAINTENANCE_PAGE_PATH = 'Home/maintenance';
    public const LOGOUT_PATH = 'Home/logout';
    public const LIVEWIRE_PATH = 'livewire/*';
    public const FILAMENT_ASSETS_PATH = 'filament/assets/*';

    /**
     * Route pattern for logout route name
     */
    public const LOGOUT_ROUTE_PATTERN = 'filament.*.auth.logout';

    /**
     * Default maintenance message (Arabic)
     */
    public const DEFAULT_MESSAGE = 'الموقع تحت الصيانة حالياً. سنعود قريباً.';

    /**
     * Public form path pattern that should bypass maintenance mode
     */
    public const PUBLIC_FORM_PATH = 'WelcomeForm*';

    /**
     * Get all paths that should be excluded from maintenance mode checks
     * 
     * @return array<int, string>
     */
    public static function getExcludedPaths(): array
    {
        return [
            self::MAINTENANCE_PAGE_PATH,
            self::LOGOUT_PATH,
            self::LIVEWIRE_PATH,
            self::FILAMENT_ASSETS_PATH,
            self::PUBLIC_FORM_PATH,
        ];
    }

    /**
     * Check if a request path should be excluded from maintenance mode
     * 
     * @param string $path
     * @return bool
     */
    public static function isExcludedPath(string $path): bool
    {
        foreach (self::getExcludedPaths() as $pattern) {
            if (fnmatch($pattern, $path)) {
                return true;
            }
        }
        return false;
    }
}

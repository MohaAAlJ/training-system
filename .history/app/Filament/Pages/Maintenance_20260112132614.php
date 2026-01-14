<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Settings\TrainingSettings;
use App\Constants\MaintenanceConstants;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;

/**
 * Maintenance Page
 * 
 * Displayed to users whose roles are affected by maintenance mode.
 * Shows a custom maintenance message and provides a logout option.
 * 
 * This page is only accessible when:
 * - Maintenance mode is enabled in TrainingSettings
 * - User's role is in the maintenance_roles array
 * - User is not an admin (admins are always exempt)
 * 
 * @package App\Filament\Pages
 */
class Maintenance extends Page
{
    /**
     * Hide navigation icon (page is not in navigation)
     * 
     * @var string|\BackedEnum|null
     */
    protected static string|\BackedEnum|null $navigationIcon = null;

    /**
     * Hide from navigation menu
     * This page is only accessible via direct URL/redirect
     * 
     * @var bool
     */
    protected static bool $shouldRegisterNavigation = false;

    /**
     * Use simple layout without sidebar
     * 
     * @var string
     */
    protected static string $layout = 'filament-panels::components.layout.simple';

    /**
     * Explicit slug for the page route
     * Route will be: /Home/maintenance
     * 
     * @var string|null
     */
    protected static ?string $slug = 'maintenance';

    /**
     * Maintenance message to display
     * Populated from TrainingSettings or uses default
     * 
     * @var string
     */
    public string $maintenanceMessage = '';

    /**
     * Mount the page and load maintenance message
     * 
     * @return void
     */
    public function mount(): void
    {
        $settings = app(TrainingSettings::class);
        $this->maintenanceMessage = $settings->maintenance_message ?? MaintenanceConstants::DEFAULT_MESSAGE;
    }

    /**
     * Get the page title
     * 
     * @return string
     */
    public function getTitle(): string
    {
        return 'وضع الصيانة';
    }

    /**
     * No header actions needed on this page
     * 
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * Get the view file path
     * 
     * @return string
     */
    public function getView(): string
    {
        return 'filament.pages.maintenance';
    }
}

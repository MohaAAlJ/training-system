<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Settings\TrainingSettings;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;

class Maintenance extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = null;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $layout = 'filament-panels::components.layout.simple';

    public string $maintenanceMessage = '';

    public function mount(): void
    {
        $settings = app(TrainingSettings::class);
        $this->maintenanceMessage = $settings->maintenance_message ?? 'الموقع تحت الصيانة حالياً. سنعود قريباً.';
    }

    public function getTitle(): string
    {
        return 'وضع الصيانة';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getView(): string
    {
        return 'filament.pages.maintenance';
    }
}

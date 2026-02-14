<?php

namespace App\Filament\Resources\Stats\Pages;

use App\Filament\Resources\Stats\StatsResource;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;

class ListStats extends Page
{
    protected static string $resource = StatsResource::class;

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return 'الإحصائيات';
    }

    public function getBreadcrumb(): ?string
    {
        return 'عرض';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // \App\Filament\Widgets\ExternalPartnerActiveTraineesWidget::class,
            \App\Filament\Widgets\GTMRecentApplications::class,
            \App\Filament\Widgets\CapacityOverviewWidget::class,
            \App\Filament\Widgets\StudentsFinishingSoonWidget::class,
            \App\Filament\Widgets\AdminLatestUsers::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    public function headerWidgets(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        $widgets = $this->getHeaderWidgets();

        $sections = [];
        foreach ($widgets as $widget) {
            $widgetClass = is_string($widget) ? $widget : $widget->widget;

            $heading = $widgetClass::$heading ?? '';

            $sections[] = Section::make($heading)
                ->collapsible()
                ->persistCollapsed()
                ->compact()
                ->schema([
                    Livewire::make($widgetClass)
                        ->key($widgetClass)
                ]);
        }

        return $schema
            ->components($sections);
    }
}

<?php

namespace App\Filament\Resources\Stats\Pages;

use App\Filament\Resources\Stats\StatsResource;
use Filament\Resources\Pages\Page;

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
}

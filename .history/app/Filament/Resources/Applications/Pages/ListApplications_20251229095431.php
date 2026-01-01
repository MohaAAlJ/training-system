<?php

namespace App\Filament\Resources\Applications\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Applications\ApplicationResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة طلب')
                ->visible(static fn() => Auth::user()?->isAdmin() || Auth::user()?->isCollegeSupervisor() ?? false),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tabs::make('الكل')
                ->modifyQueryUsing(fn($query) => $query->where('status', '!=', \App\Models\Application::STATUS_REJECTED)),
            'new' => Tabs::make('الطلبات الجديدة')
                ->modifyQueryUsing(fn($query) => $query->where('status', \App\Models\Application::STATUS_NEW))
                ->badge(\App\Models\Application::where('status', \App\Models\Application::STATUS_NEW)->count()),
            'initial_approve' => Tabs::make('موافقة مبدئية')
                ->modifyQueryUsing(fn($query) => $query->where('status', \App\Models\Application::STATUS_INITIAL_APPROVE)),
            'confirmed' => Tabs::make('مؤكدة/انتظار')
                ->modifyQueryUsing(fn($query) => $query->whereIn('status', [\App\Models\Application::STATUS_CONFIRMATION, \App\Models\Application::STATUS_WAITING_LIST])),
            'training' => Tabs::make('قيد التدريب')
                ->modifyQueryUsing(fn($query) => $query->where('status', \App\Models\Application::STATUS_STARTED_TRAINING)),
            'finished' => Tabs::make('منتهي')
                ->modifyQueryUsing(fn($query) => $query->where('status', \App\Models\Application::STATUS_ENDED_TRAINING)),
            'rejected' => Tabs::make('مرفوض')
                ->modifyQueryUsing(fn($query) => $query->where('status', \App\Models\Application::STATUS_REJECTED)),
        ];
    }
}

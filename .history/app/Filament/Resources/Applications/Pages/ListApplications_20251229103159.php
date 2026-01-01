<?php

namespace App\Filament\Resources\Applications\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Applications\ApplicationResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
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
            'all' => Tab::make('الكل')
                ->modifyQueryUsing(fn($query) => $query->where('status', '!=', \App\Models\Application::STATUS_REJECTED))
                ->badge(\App\Models\Application::where('status', '!=', \App\Models\Application::STATUS_REJECTED)->count()),
            'new' => Tab::make('الطلبات الجديدة')
                ->modifyQueryUsing(fn($query) => $query->where('status', \App\Models\Application::STATUS_NEW))
                ->badge(\App\Models\Application::where('status', \App\Models\Application::STATUS_NEW)->count()),
            'initial_approve' => Tab::make('موافقة مبدئية')
                ->modifyQueryUsing(fn($query) => $query->where('status', \App\Models\Application::STATUS_INITIAL_APPROVE))
                ->badge(\App\Models\Application::where('status', \App\Models\Application::STATUS_INITIAL_APPROVE)->count()),
            'confirmed' => Tab::make('مؤكدة/انتظار')
                ->modifyQueryUsing(fn($query) => $query->whereIn('status', [\App\Models\Application::STATUS_CONFIRMATION, \App\Models\Application::STATUS_WAITING_LIST]))
                ->badge(\App\Models\Application::whereIn('status', [\App\Models\Application::STATUS_CONFIRMATION, \App\Models\Application::STATUS_WAITING_LIST])->count()),
            'training' => Tab::make('قيد التدريب')
                ->modifyQueryUsing(fn($query) => $query->where('status', \App\Models\Application::STATUS_STARTED_TRAINING))
                ->badge(\App\Models\Application::where('status', \App\Models\Application::STATUS_STARTED_TRAINING)->count()),
            'finished' => Tab::make('منتهي')
                ->modifyQueryUsing(fn($query) => $query->where('status', \App\Models\Application::STATUS_ENDED_TRAINING))
                ->badge(\App\Models\Application::where('status', \App\Models\Application::STATUS_ENDED_TRAINING)->count()),
            'rejected' => Tab::make('مرفوض')
                ->modifyQueryUsing(fn($query) => $query->where('status', \App\Models\Application::STATUS_REJECTED))
                ->badge(\App\Models\Application::where('status', \App\Models\Application::STATUS_REJECTED)->count()),
        ];
    }
}

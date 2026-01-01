<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Application\Pages\CreateApplication;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Applications\ApplicationResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

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
            'all' => \Filament\Resources\Components\Tab::make('الكل')
                ->modifyQueryUsing(fn($query) => $query->where('status', '!=', \App\Models\Application::STATUS_REJECTED)),
            'new' => \Filament\Resources\Components\Tab::make('الطلبات الجديدة')
                ->modifyQueryUsing(fn($query) => $query->where('status', \App\Models\Application::STATUS_NEW))
                ->badge(\App\Models\Application::where('status', \App\Models\Application::STATUS_NEW)->count()),
            'initial_approve' => \Filament\Resources\Components\Tab::make('موافقة مبدئية')
                ->modifyQueryUsing(fn($query) => $query->where('status', \App\Models\Application::STATUS_INITIAL_APPROVE)),
            'confirmed' => \Filament\Resources\Components\Tab::make('مؤكدة/انتظار')
                ->modifyQueryUsing(fn($query) => $query->whereIn('status', [\App\Models\Application::STATUS_CONFIRMATION, \App\Models\Application::STATUS_WAITING_LIST])),
            'training' => \Filament\Resources\Components\Tab::make('قيد التدريب')
                ->modifyQueryUsing(fn($query) => $query->where('status', \App\Models\Application::STATUS_STARTED_TRAINING)),
            'finished' => \Filament\Resources\Components\Tab::make('منتهي')
                ->modifyQueryUsing(fn($query) => $query->where('status', \App\Models\Application::STATUS_ENDED_TRAINING)),
            'rejected' => \Filament\Resources\Components\Tab::make('مرفوض')
                ->modifyQueryUsing(fn($query) => $query->where('status', \App\Models\Application::STATUS_REJECTED)),
        ];
    }
}

<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Enums\ApplicationStatus;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\Application;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\Auth;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة طلب')
                ->visible(static fn() => Auth::user()?->isAdmin() || Auth::user()?->isCollegeSupervisor() ?? false),
        ];
    }

    public function getTabs(): array
    {
        $user = Auth::user();
        if (! ($user?->isGeneralTrainingManager())) {
            return [];
        }

        return [
            'all' => Tab::make('الكل')
                ->modifyQueryUsing(fn($query) => $query->where('status', '!=', ApplicationStatus::REJECTED))
                ->badge(Application::where('status', '!=', ApplicationStatus::REJECTED)->count()),

            'new' => Tab::make('الطلبات الجديدة')
                ->modifyQueryUsing(fn($query) => $query->where('status', ApplicationStatus::NEW))
                ->badge(Application::where('status', ApplicationStatus::NEW)->count()),

            'initial_approve' => Tab::make('موافقة مبدئية')
                ->modifyQueryUsing(fn($query) => $query->where('status', ApplicationStatus::INITIAL_APPROVE))
                ->badge(Application::where('status', ApplicationStatus::INITIAL_APPROVE)->count()),

            'confirmed' => Tab::make('مؤكدة/انتظار')
                ->modifyQueryUsing(fn($query) => $query->whereIn('status', [ApplicationStatus::CONFIRMATION, ApplicationStatus::WAITING_LIST]))
                ->badge(Application::whereIn('status', [ApplicationStatus::CONFIRMATION, ApplicationStatus::WAITING_LIST])->count()),

            'training' => Tab::make('قيد التدريب')
                ->modifyQueryUsing(fn($query) => $query->where('status', ApplicationStatus::STARTED_TRAINING))
                ->badge(Application::where('status', ApplicationStatus::STARTED_TRAINING)->count()),

            'finished' => Tab::make('منتهي')
                ->modifyQueryUsing(fn($query) => $query->where('status', ApplicationStatus::ENDED_TRAINING))
                ->badge(Application::where('status', ApplicationStatus::ENDED_TRAINING)->count()),

            'rejected' => Tab::make('مرفوض')
                ->modifyQueryUsing(fn($query) => $query->where('status', ApplicationStatus::REJECTED))
                ->badge(Application::where('status', ApplicationStatus::REJECTED)->count()),
        ];
    }
}

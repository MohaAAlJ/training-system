<?php

namespace App\Filament\Resources\Applications\Pages;

// use App\Enums\ApplicationStatus;
// use App\Enums\TrainingType;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Filament\Resources\Applications\Tables\ApplicationsTable;
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
                ->visible(static fn() => Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isCollegeSupervisor())),

            ApplicationsTable::getImportExcelAction()
                ->label('إضافة طلبات'),
        ];
    }

    public function getTabs(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        $tabs = [];
        $baseQuery = static::getResource()::getEloquentQuery();

        // 1. "All" Tab
        $tabs['all'] = Tab::make('الكل')
            ->modifyQueryUsing(fn($query) => $query->where('status', '!=', Application::STATUS_REJECTED))
            ->badge($baseQuery->clone()->where('status', '!=', Application::STATUS_REJECTED)->count());

        // 2. Role-specific Status Tabs

        // NEW (Admin/GTM only)
        if ($user->isAdmin() || $user->isGeneralTrainingManager()) {
            $tabs['new'] = Tab::make(Application::getStatusLabel(Application::STATUS_NEW))
                ->modifyQueryUsing(fn($query) => $query->where('status', Application::STATUS_NEW))
                ->badge($baseQuery->clone()->where('status', Application::STATUS_NEW)->count());
        }

        // INITIAL_APPROVE (Admin/GTM/MOH/College)
        if ($user->isAdmin() || $user->isGeneralTrainingManager() || $user->isMinistry() || $user->isCollegeSupervisor()) {
            $label = ($user->isGeneralTrainingManager()) ? 'في انتظار التأكيد' : Application::getStatusLabel(Application::STATUS_INITIAL_APPROVE);

            $tabs['initial_approve'] = Tab::make($label)
                ->modifyQueryUsing(fn($query) => $query->where('status', Application::STATUS_INITIAL_APPROVE))
                ->badge($baseQuery->clone()->where('status', Application::STATUS_INITIAL_APPROVE)->count());
        }

        // CONFIRMATION (Admin/GTM/MOH/College)
        if ($user->isAdmin() || $user->isGeneralTrainingManager() || $user->isMinistry() || $user->isCollegeSupervisor()) {
            $tabs['confirmed'] = Tab::make(Application::getStatusLabel(Application::STATUS_CONFIRMATION))
                ->modifyQueryUsing(fn($query) => $query->where('status', Application::STATUS_CONFIRMATION))
                ->badge($baseQuery->clone()->where('status', Application::STATUS_CONFIRMATION)->count());
        }

        // WAITING_LIST (Everyone has access)
        $tabs['waiting_list'] = Tab::make(Application::getStatusLabel(Application::STATUS_WAITING_LIST))
            ->modifyQueryUsing(fn($query) => $query->where('status', Application::STATUS_WAITING_LIST))
            ->badge($baseQuery->clone()->where('status', Application::STATUS_WAITING_LIST)->count());

        // TRAINING (Everyone)
        $tabs['training'] = Tab::make(Application::getStatusLabel(Application::STATUS_STARTED_TRAINING))
            ->modifyQueryUsing(fn($query) => $query->where('status', Application::STATUS_STARTED_TRAINING))
            ->badge($baseQuery->clone()->where('status', Application::STATUS_STARTED_TRAINING)->count());

        // FINISHED (Everyone)
        $tabs['finished'] = Tab::make(Application::getStatusLabel(Application::STATUS_ENDED_TRAINING))
            ->modifyQueryUsing(fn($query) => $query->where('status', Application::STATUS_ENDED_TRAINING))
            ->badge($baseQuery->clone()->where('status', Application::STATUS_ENDED_TRAINING)->count());

        // REJECTED (Admin/GTM only)
        if ($user->isAdmin() || $user->isGeneralTrainingManager()) {
            $tabs['rejected'] = Tab::make(Application::getStatusLabel(Application::STATUS_REJECTED))
                ->modifyQueryUsing(fn($query) => $query->where('status', Application::STATUS_REJECTED))
                ->badge($baseQuery->clone()->where('status', Application::STATUS_REJECTED)->count());

            // Training Type Tabs (Admin/GTM only as they see both types)
            // $tabs['separator'] = Tab::make('---')
            //     ->modifyQueryUsing(fn($query) => $query)
            //     ->badge(null);

            $tabs['university_training'] = Tab::make(Application::getTrainingTypeLabel(Application::UNIVERSITY))
                ->modifyQueryUsing(fn($query) => $query->where('training_type', Application::UNIVERSITY))
                ->badge($baseQuery->clone()->where('training_type', Application::UNIVERSITY)->count());

            $tabs['practice_training'] = Tab::make(Application::getTrainingTypeLabel(Application::PRACTICE))
                ->modifyQueryUsing(fn($query) => $query->where('training_type', Application::PRACTICE))
                ->badge($baseQuery->clone()->where('training_type', Application::PRACTICE)->count());
        }

        return $tabs;
    }
}

<?php

namespace App\Filament\Resources\Applications\Pages;

use filament\App\Filament\Resources\Applications\Pages\CreateApplications;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Applications\ApplicationsResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationsResource::class;

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
        $user = Auth::user();

        // Only show tabs for HOD, HOA, and Section Head
        if (!$user->isDepartment() && !$user->isHOA() && !$user->isSectionHead()) {
            return [];
        }

        return [
            'active' => Tab::make('نشط')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', \App\Helpers\Constans::STATUS_STRATED_TRAINING)),
            'finished' => Tab::make('منتهي')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', \App\Helpers\Constans::STATUS_ENDED_TRAINING)),
            'all' => Tab::make('الكل'),
        ];
    }
}

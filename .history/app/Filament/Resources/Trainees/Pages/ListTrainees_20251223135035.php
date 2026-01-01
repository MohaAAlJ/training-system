<?php

namespace App\Filament\Resources\Trainees\Pages;

use App\Filament\Resources\Trainees\TraineesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListTrainees extends ListRecords
{
    protected static string $resource = TraineesResource::class;

    protected function getHeaderActions(): array
    {
        return [];
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
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('applications', function ($q) {
                    $q->where('status', \App\Helpers\Constans::STATUS_STRATED_TRAINING);
                })),
            'finished' => Tab::make('منتهي')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('applications', function ($q) {
                    $q->where('status', \App\Helpers\Constans::STATUS_ENDED_TRAINING);
                })),
            'all' => Tab::make('الكل'),
        ];
    }
}

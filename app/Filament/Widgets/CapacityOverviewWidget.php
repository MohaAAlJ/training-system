<?php

namespace App\Filament\Widgets;

use App\Helpers\Constans;
use App\Models\Sections;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class CapacityOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 2;

    protected static ?string $heading = 'السعة الاستيعابية للأقسام';

    public static function canView(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        // Allowed for: GTM, Admin, HOA, HOM, Department Head, Section Head
        return in_array($user->role, [
            Constans::ROLE_GTM,
            Constans::ROLE_ADMIN,
            Constans::ROLE_HOA,
            Constans::ROLE_HOM,
            Constans::ROLE_DEPARTMENT,
            Constans::ROLE_SECTION,
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Sections::query()
                    ->with(['administrative', 'department'])
                    ->withCount(['applications as active_trainees_count' => function (Builder $query) {
                        $query->where('status', Constans::STATUS_STRATED_TRAINING);
                    }])
            )
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();

                if ($user->isGeneralTrainingManager() || $user->isAdmin()) {
                    // All sections
                    return $query;
                }

                if ($user->isMedicalManager()) { // ROLE_HOM (7)
                    // Sections in Medical Departments
                    return $query->whereHas('department', function ($q) {
                        $q->where('is_medical', true);
                    });
                }

                if ($user->isHOA()) { // ROLE_HOA (6)
                    // Sections in their Administrative
                    // Assuming user has administrative relationship
                    // Or checking logical link (User -> Administrative -> Sections)
                    if ($user->administrative) {
                        return $query->where('administrative_id', $user->administrative->id);
                    }
                    // Fallback or safety if no administrative assigned
                    return $query->whereRaw('0 = 1');
                }

                if ($user->isDepartmentHead()) { // ROLE_DEPARTMENT (2)
                    // Sections in their Department
                    if ($user->department) {
                         return $query->where('department_id', $user->department->id);
                    }
                     return $query->whereRaw('0 = 1');
                }

                if ($user->isSectionHead()) { // ROLE_SECTION (3)
                    // Their Section
                    // User -> sections (hasOne)
                    if ($user->sections) {
                        return $query->where('id', $user->sections->id);
                    }
                    return $query->whereRaw('0 = 1');
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('name_location')
                    ->label('القسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('administrative.title')
                    ->label('الإدارة')
                    ->searchable()
                    ->sortable()
                    ->visible(fn() => Auth::user()->isGeneralTrainingManager() || Auth::user()->isAdmin() || Auth::user()->isMedicalManager()),
                Tables\Columns\TextColumn::make('total_capacity')
                    ->label('السعة الكلية')
                    ->sortable(),
                Tables\Columns\TextColumn::make('active_trainees_count')
                    ->label('مشغول (متدرب نشط)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('available_capacity')
                    ->label('متاح')
                    ->state(function (Sections $record) {
                        return max(0, $record->total_capacity - $record->active_trainees_count);
                    }),
            ])
            ->defaultSort('name_location');
    }
}

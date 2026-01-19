<?php

namespace App\Filament\Widgets;

use App\Models\Application;

use App\Models\User;

use App\Helpers\Constants;
use App\Models\Section;
use App\Models\Administrative;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class CapacityOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'إحصائيات السعة الاستيعابية للأقسام';

    public function getHeading(): string | Heading
    {
        $count = $this->table(app(\Filament\Tables\Table::class))->getQuery()->count();
        return 'إحصائيات السعة الاستيعابية للأقسام (' . $count . ')';
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        // Allowed for: GTM, Admin, HOA, HOM, Department Head, Section Head
        return !request()->routeIs('filament.home.pages.dashboard') && in_array($user->role, [
            User::ROLE_GTM,
            User::ROLE_ADMIN,
            User::ROLE_HOA,
            User::ROLE_HOM,
            User::ROLE_DEPARTMENT,
            User::ROLE_SECTION,
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Section::query()
                    ->with(['administrative', 'department'])
                    ->withCount(['applications as active_Trainee_count' => function (Builder $query) {
                        $query->where('status', Application::STATUS_STARTED_TRAINING);
                    }])
            )
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();

                if ($user->isGeneralTrainingManager() || $user->isAdmin()) {
                    // All Section
                    return $query;
                }

                if ($user->isMedicalManager()) { // ROLE_HOM (7)
                    // Medical Manager should see ONLY medical Department WITHIN their administrative unit
                    $adminUnit = Administrative::where('medical_head_user_id', $user->id)->first();
                    if ($adminUnit) {
                        return $query->where('administrative_id', $adminUnit->id)
                            ->whereHas('department', function ($q) {
                                $q->where('is_medical', true);
                            });
                    }
                    return $query->whereRaw('0 = 1');
                }

                if ($user->isHOA()) { // ROLE_HOA (6)
                    // HOA should see ALL Department/Section WITHIN their administrative unit
                    $adminUnit = Administrative::where('user_id', $user->id)->first();
                    if ($adminUnit) {
                        return $query->where('administrative_id', $adminUnit->id);
                    }
                    return $query->whereRaw('0 = 1');
                }

                if ($user->isDepartmentHead()) { // ROLE_DEPARTMENT (2)
                    // Section in their Department
                    if ($user->department) {
                        return $query->where('department_id', $user->department->id);
                    }
                    return $query->whereRaw('0 = 1');
                }

                if ($user->isSectionHead()) { // ROLE_SECTION (3)
                    // Their Section
                    // User -> Section (hasOne)
                    if ($user->Section) {
                        return $query->where('id', $user->Section->id);
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
                Tables\Columns\TextColumn::make('capacity')
                    ->label('السعة الكلية')
                    ->sortable(),
                Tables\Columns\TextColumn::make('active_Trainee_count')
                    ->label('مشغول')
                    ->sortable(),
                Tables\Columns\TextColumn::make('available_capacity')
                    ->label('متاح')
                    ->state(function (Section $record) {
                        return max(0, $record->capacity - $record->active_Trainee_count);
                    }),
            ])
            ->defaultSort('name_location');
    }
}

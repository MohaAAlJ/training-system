<?php

namespace App\Filament\Widgets;

use App\Helpers\Constans;
use App\Models\Applications;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\ViewAction;
use Illuminate\Support\Facades\Lang;
use Carbon\Carbon;
use Filament\Actions\Action;

class StudentsFinishingSoonWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'طلاب يقترب موعد انتهاء تدريبهم';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Applications::query()
                    ->where('status', Constans::STATUS_STRATED_TRAINING)
                    ->where('end_date', '>=', Carbon::today())
                    ->where('end_date', '<=', Carbon::today()->addDays(3)) // Within next 3 days
                    ->with(['trainee', 'section', 'department'])
            )
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();

                if ($user->isGeneralTrainingManager() || $user->isAdmin()) {
                    return $query;
                }

                if ($user->isMedicalManager()) { // ROLE_HOM
                    return $query->whereHas('department', function ($q) {
                        $q->where('is_medical', true);
                    });
                }

                if ($user->isHOA()) { // ROLE_HOA
                    if ($user->administrative) {
                        return $query->where('administrative_id', $user->administrative->id);
                    }
                    return $query->whereRaw('0 = 1');
                }

                if ($user->isDepartmentHead()) { // ROLE_DEPARTMENT
                    if ($user->department) {
                        return $query->where('department_id', $user->department->id);
                    }
                    return $query->whereRaw('0 = 1');
                }

                if ($user->isSectionHead()) { // ROLE_SECTION
                    if ($user->sections) {
                        return $query->where('section_id', $user->sections->id);
                    }
                    return $query->whereRaw('0 = 1');
                }

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('trainee.full_name')
                    ->label('اسم المتدرب')
                    ->searchable(),
                Tables\Columns\TextColumn::make('section.name_location')
                    ->label('القسم')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->date()
                    ->sortable()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('days_left')
                    ->label('الأيام المتبقية')
                    ->state(function (Applications $record) {
                        $days = Carbon::today()->diffInDays($record->end_date, false);
                        return $days <= 0 ? 'ينتهي اليوم' : $days . ' أيام';
                    })
                    ->badge()
                    ->color(fn ($state) => $state === 'ينتهي اليوم' ? 'danger' : 'warning'),
            ])
            ->actions([
                Action::make('view')
                    ->label('عرض')
                    ->url(fn (Applications $record): string => \App\Filament\Resources\Applications\ApplicationsResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('لا يوجد طلاب تنتهي فترة تدريبهم قريباً');
    }
}

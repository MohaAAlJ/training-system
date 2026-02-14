<?php

namespace App\Filament\Widgets;

use App\Models\Section;
use App\Models\User;
use App\Models\Application;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CriticalCapacityWatchlist extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;
    protected static ?string $heading = 'قائمة المراقبة (أقسام حرجة)';

    public static function canView(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        return !in_array($user->role, [
            User::ROLE_COLLEGE,
            User::ROLE_MOH,
            User::ROLE_SECTION,
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $user = Auth::user();
                $query = Section::query()
                    ->active() // Strictly active sections
                    ->whereHas('administrative', fn ($q) => $q->active()) // Active Administrative
                    ->whereHas('department', fn ($q) => $q->active()) // Active Department
                    ->where('capacity', '>', 0);

                if ($user->isAdministrative()) {
                    $query->where('administrative_id', $user->administrative?->id);
                } elseif ($user->isDepartmentHead()) {
                    $query->where('department_id', $user->department?->id);
                } elseif ($user->isMedicalManager()) {
                    $query->whereHas('department', fn ($q) => $q->where('is_medical', true));
                }

                return $query->withCount(['applications as active_trainees' => function (Builder $query) {
                        $query->where('status', Application::STATUS_STARTED_TRAINING);
                    }])

                    ->whereRaw('capacity > 0')
                    // Calculate ratio in WHERE clause to avoid HAVING/GROUP BY issues
                    ->whereRaw('(
                        (select count(*) from `applications`
                         where `sections`.`id` = `applications`.`section_id`
                         and `status` = ?
                         and `applications`.`deleted_at` is null
                        ) / capacity
                    ) >= 0.9', [Application::STATUS_STARTED_TRAINING])
                    ->orderByRaw('(
                        (select count(*) from `applications`
                         where `sections`.`id` = `applications`.`section_id`
                         and `status` = ?
                         and `applications`.`deleted_at` is null
                        ) / capacity
                    ) DESC', [Application::STATUS_STARTED_TRAINING]);
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('القسم')
                    ->description(fn (Section $record) => $record->administrative?->name ?? '-'),
                Tables\Columns\TextColumn::make('occupancy')
                    ->label('الإشغال')
                    ->state(fn (Section $record) => $record->getCapacityStats()['used'] . ' / ' . $record->getCapacityStats()['total'])
                    ->badge()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('available')
                    ->label('المتبقي')
                    ->state(fn (Section $record) => $record->getCapacityStats()['available'])
                    ->color('danger')
                    ->weight('bold'),
            ])
            ->paginated(false)
            ->emptyStateHeading('لا توجد أقسام في حالة حرجة')
            ->emptyStateDescription('جميع الأقسام لديها سعة متاحة حالياً.');
    }
}

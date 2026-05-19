<?php

namespace App\Filament\Widgets;

use App\Models\Administrative;
use App\Models\Application;
use App\Models\Department;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AdministrativeCapacityOverview extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 1;
    protected static ?string $heading = 'السعة الاستيعابية للإدارات';

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->isTrainingManagerLike() || $user->isMonitor());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Administrative::query()
                    ->active()
                    ->when(Auth::user()?->isAssistantTrainingManager(), function ($q) {
                        $managedDepartmentIds = Auth::user()->managedDepartmentIds();
                        if (empty($managedDepartmentIds)) {
                            return $q->whereRaw('0 = 1');
                        }
                        return $q->whereHas('sections.departments', fn($d) => $d->whereIn('departments.id', $managedDepartmentIds)->visible());
                    })
                    ->withSum(['sections as total_capacity' => fn($q) => $q->active()
                        ->when(Auth::user()?->isAssistantTrainingManager(), fn($sectionQuery) => $sectionQuery->whereHas('departments', fn($d) => $d->whereIn('departments.id', Auth::user()->managedDepartmentIds())->visible()))
                    ], 'capacity')
                    ->addSelect([
                        'used_capacity' => Application::query()
                            ->selectRaw('COUNT(*)')
                            ->join('sections', 'applications.section_id', '=', 'sections.id')
                            ->whereColumn('sections.administrative_id', 'administratives.id')
                            ->where('applications.status', Application::STATUS_STARTED_TRAINING)
                            ->when(Auth::user()?->isAssistantTrainingManager(), fn($applicationQuery) => $applicationQuery->whereExists(
                                Department::query()
                                    ->join('department_section', 'departments.id', '=', 'department_section.department_id')
                                    ->whereColumn('department_section.section_id', 'applications.section_id')
                                    ->whereIn('departments.id', Auth::user()->managedDepartmentIds())
                                    ->where('departments.visible', true)
                                    ->selectRaw('1')
                                    ->toBase()
                            ))
                            ->whereNull('applications.deleted_at'),
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الإدارة')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('capacity_stats')
                    ->label('الاستيعاب')
                    ->state(fn(Administrative $record) =>
                        (int) ($record->used_capacity ?? 0) . ' / ' . (int) ($record->total_capacity ?? 0)
                    ),

                Tables\Columns\TextColumn::make('saturation')
                    ->label('نسبة الإشغال')
                    ->badge()
                    ->state(function (Administrative $record): float {
                        $total = (int) ($record->total_capacity ?? 0);
                        $used = (int) ($record->used_capacity ?? 0);
                        return $total > 0 ? ($used / $total) * 100 : 0.0;
                    })
                    ->formatStateUsing(fn(float $state) => number_format($state, 1) . '%')
                    ->color(function (float $state): string {
                        if ($state >= 90) return 'danger';
                        if ($state >= 75) return 'warning';
                        return 'success';
                    }),

                Tables\Columns\TextColumn::make('available')
                    ->label('المتاح')
                    ->state(fn(Administrative $record): int =>
                        max(0, (int) ($record->total_capacity ?? 0) - (int) ($record->used_capacity ?? 0))
                    )
                    ->color(fn(int $state): string => $state <= 0 ? 'danger' : 'success')
                    ->sortable(query: fn(Builder $query, string $direction): Builder =>
                        $query->orderByRaw("GREATEST(COALESCE(total_capacity, 0) - COALESCE(used_capacity, 0), 0) {$direction}")
                    ),
            ])
            ->paginated(false);
    }
}

<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasRoleBasedQueryScopes;
use App\Models\Application;
use App\Models\Section;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CriticalCapacityWatchlist extends BaseWidget
{
    use HasRoleBasedQueryScopes;

    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;
    protected static ?string $heading = 'قائمة المراقبة (أقسام حرجة)';

    public static function canView(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        return in_array($user->role, [
            User::ROLE_ADMIN,
            User::ROLE_GTM,
            User::ROLE_ASSISTANT_TRAINING_MANAGER,
            User::ROLE_MONITOR,
            User::ROLE_HOA,
            User::ROLE_HOM,
            User::ROLE_DEPARTMENT,
        ]);
    }

    public function table(Table $table): Table
    {
        // Reused in both whereRaw and orderByRaw to avoid repetition.
        $activeCountSql = "(select count(*) from `applications`
            where `sections`.`id` = `applications`.`section_id`
            and `status` = ?
            and `applications`.`deleted_at` is null)";

        return $table
            ->query(function () use ($activeCountSql) {
                $user = Auth::user();

                $query = Section::query()
                    ->active()
                    ->whereHas('administrative', fn($q) => $q->active())
                    ->whereHas('departments', fn($q) => $q->active()->visible())
                    ->where('capacity', '>', 0)
                    ->with('administrative')
                    ->withCount(['applications as active_trainees' => fn(Builder $q) =>
                        $q->where('status', Application::STATUS_STARTED_TRAINING)
                    ]);

                $query = match (true) {
                    $user->isAdministrative() => $this->applyHoaQuery($query, $user),
                    $user->isAssistantTrainingManager() => $this->applyAssistantTrainingManagerSectionQuery($query, $user),
                    $user->isDepartmentHead() => $this->applyDepartmentHeadQuery($query, $user),
                    $user->isMedicalManager() => $this->applyMedicalManagerQuery($query, $user),
                    default => $query,
                };

                return $query
                    ->whereRaw("({$activeCountSql} / capacity) >= 0.9", [Application::STATUS_STARTED_TRAINING])
                    ->orderByRaw("{$activeCountSql} DESC", [Application::STATUS_STARTED_TRAINING]);
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('القسم')
                    ->description(fn(Section $record) => $record->administrative?->name ?? '-'),

                Tables\Columns\TextColumn::make('occupancy')
                    ->label('الإشغال')
                    ->state(fn(Section $record) => $record->active_trainees . ' / ' . $record->capacity)
                    ->badge()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('available')
                    ->label('المتبقي')
                    ->state(fn(Section $record) => max(0, $record->capacity - $record->active_trainees))
                    ->color('danger')
                    ->weight('bold'),
            ])
            ->paginated(false)
            ->emptyStateHeading('لا توجد أقسام في حالة حرجة')
            ->emptyStateDescription('جميع الأقسام لديها سعة متاحة حالياً.');
    }
}

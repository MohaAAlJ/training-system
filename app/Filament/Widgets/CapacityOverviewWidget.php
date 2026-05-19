<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasRoleBasedQueryScopes;
use App\Models\Administrative;
use App\Models\Application;
use App\Models\Section;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CapacityOverviewWidget extends BaseWidget
{
    use HasRoleBasedQueryScopes;

    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 1;
    protected static ?string $heading = 'إحصائيات السعة الاستيعابية للأقسام';

    public static function canView(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        return !request()->routeIs('filament.home.pages.dashboard') && in_array($user->role, [
            User::ROLE_GTM,
            User::ROLE_ASSISTANT_TRAINING_MANAGER,
            User::ROLE_ADMIN,
            User::ROLE_MONITOR,
            User::ROLE_HOA,
            User::ROLE_HOM,
            User::ROLE_DEPARTMENT,
            User::ROLE_SECTION,
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(null)
            ->emptyStateHeading('')
            ->emptyStateDescription('')
            ->emptyStateIcon(null)
            ->query(
                Section::query()
                    ->active()
                    ->with(['administrative', 'departments' => fn($q) => $q->visible()])
                    ->withCount(['applications as active_trainee_count' => fn(Builder $query) =>
                        $query->where('applications.status', Application::STATUS_STARTED_TRAINING)
                    ])
            )
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();

                return match (true) {
                    $user->isGeneralTrainingManager() || $user->isAdmin() || $user->isMonitor() => $query,
                    $user->isAssistantTrainingManager() => $this->applyAssistantTrainingManagerSectionQuery($query, $user),
                    $user->isMedicalManager() => $this->applyMedicalManagerQuery($query, $user),
                    $user->isHOA() => $this->applyHoaQuery($query, $user),
                    $user->isDepartmentHead() => $this->applyDepartmentHeadQuery($query, $user),
                    $user->isSectionHead() => $this->applySectionHeadQuery($query, $user),
                    $user->isMinistry() => $this->applyMohQuery($query, $user),
                    default => $query->whereRaw('0 = 1'),
                };
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('القسم')
                    ->description(fn(Section $record) => $record->administrative?->name ?? '-')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('departments.name')
                    ->label('الدوائر')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('administrative.name')
                    ->label('الإدارة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->label('السعة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('active_trainee_count')
                    ->label('مشغول')
                    ->sortable(),
                Tables\Columns\TextColumn::make('available_capacity')
                    ->label('متاح')
                    ->state(fn(Section $record) => max(0, $record->capacity - $record->active_trainee_count))
                    ->sortable(query: fn(Builder $query, string $direction): Builder =>
                        $query->orderByRaw("GREATEST(capacity - active_trainee_count, 0) {$direction}")
                    ),
            ])
            ->recordUrl(
                fn(Section $record): string => \App\Filament\Resources\Sections\SectionResource::getUrl('view', ['record' => $record]),
            )
            ->defaultSort('name');
    }

    private function applyMohQuery(Builder $query, User $user): Builder
    {
        if ($user->mohDepartment && $user->mohDepartment()->active()->doesntExist()) {
            return $query->whereRaw('0 = 1');
        }

        if ($user->mohDepartment) {
            return $query->whereHas('departments', fn($q) => $q->where('departments.id', $user->mohDepartment->id)->visible());
        }

        // Unlinked MOH — see all sections with visible departments
        return $query->whereHas('departments', fn($q) => $q->visible());
    }
}

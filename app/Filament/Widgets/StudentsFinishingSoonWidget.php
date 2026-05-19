<?php

namespace App\Filament\Widgets;

use App\Models\Administrative;
use App\Models\Application;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StudentsFinishingSoonWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 1;

    public static ?string $heading = 'متابعة المتدربين المنتهين قريباً';

    public function getHeading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return '';
    }

    public function isCollapsed(): bool
    {
        return $this->isEmpty();
    }

    protected function isEmpty(): bool
    {
        return $this->getTableQuery()->doesntExist();
    }

    public static function canView(): bool
    {
        // Hide from the dashboard (auto-discovery); only show on Stats page
        if (request()->routeIs('filament.home.pages.dashboard')) {
            return false;
        }

        $user = Auth::user();
        if (!$user) return false;

        return in_array($user->role, [
            User::ROLE_GTM,
            User::ROLE_ASSISTANT_TRAINING_MANAGER,
            User::ROLE_ADMIN,
            User::ROLE_MONITOR,
            User::ROLE_HOA,
            User::ROLE_HOM,
            User::ROLE_DEPARTMENT,
            User::ROLE_SECTION,
            User::ROLE_MOH,
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
                Application::query()
                    ->where('status', Application::STATUS_STARTED_TRAINING)
                    ->where('end_date', '>=', Carbon::today())
                    ->whereHas('section', fn($q) => $q->active())
                    ->with(['trainee', 'section.departments' => fn($q) => $q->visible(), 'section.administrative'])
            )
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();

                return match (true) {
                    $user->isGeneralTrainingManager() || $user->isAdmin() || $user->isMonitor() => $query,
                    $user->isAssistantTrainingManager() => $query->forManagedDepartments($user->managedDepartmentIds()),
                    $user->isMedicalManager() => $this->applyMedicalManagerQuery($query, $user),
                    $user->isHOA() => $this->applyHoaQuery($query, $user),
                    $user->isMinistry() => $this->applyMohQuery($query, $user),
                    $user->isDepartmentHead() => $this->applyDepartmentHeadQuery($query, $user),
                    $user->isSectionHead() => $this->applySectionHeadQuery($query, $user),
                    default => $query,
                };
            })
            ->columns([
                Tables\Columns\TextColumn::make('trainee.full_name')
                    ->label('اسم المتدرب')
                    ->searchable(),
                Tables\Columns\TextColumn::make('section.name')
                    ->label('القسم')
                    ->formatStateUsing(fn(Application $record) => $record->section?->administrative?->name . ' - ' . $record->section?->name)
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->date()
                    ->sortable()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('days_left')
                    ->label('الأيام المتبقية')
                    ->state(function (Application $record) {
                        $days = Carbon::today()->diffInDays($record->end_date, false);
                        return $days <= 0 ? 'ينتهي اليوم' : $days . ' أيام';
                    })
                    ->badge()
                    ->color(fn($state) => $state === 'ينتهي اليوم' ? 'danger' : 'warning'),
            ])
            ->recordUrl(
                fn(Application $record): string => \App\Filament\Resources\Applications\ApplicationResource::getUrl('view', ['record' => $record->id]),
            )
            ->filters([
                Tables\Filters\SelectFilter::make('days_range')
                    ->label('الفترة الزمنية')
                    ->options([
                        '3' => '3 أيام',
                        '7' => 'أسبوع',
                        '14' => 'أسبوعين',
                    ])
                    ->default('3')
                    ->query(function (Builder $query, array $data) {
                        $days = (int) ($data['value'] ?? 3);
                        return $query->where('end_date', '>=', Carbon::today())
                            ->where('end_date', '<=', Carbon::today()->addDays($days));
                    }),
            ])
            ->actions([
                Action::make('view')
                    ->label('عرض')
                    ->url(fn(Application $record): string => \App\Filament\Resources\Applications\ApplicationResource::getUrl('view', ['record' => $record->id])),
            ])
            ->emptyStateHeading('لا يوجد طلاب تنتهي فترة تدريبهم قريباً');
    }

    private function applyMedicalManagerQuery(Builder $query, User $user): Builder
    {
        $adminUnit = Administrative::where('medical_head_user_id', $user->id)->active()->first();
        if (!$adminUnit) {
            return $query->whereRaw('0 = 1');
        }
        return $query->whereHas('section', function ($q) use ($adminUnit) {
            $q->where('administrative_id', $adminUnit->id)
                ->whereHas('departments', fn($d) => $d->where('is_medical', true)->active()->visible());
        });
    }

    private function applyHoaQuery(Builder $query, User $user): Builder
    {
        if ($user->administrative()->active()->exists()) {
            return $query->whereHas('section', function ($q) use ($user) {
                $q->where('administrative_id', $user->administrative->id);
            });
        }
        return $query->whereRaw('0 = 1');
    }

    private function applyMohQuery(Builder $query, User $user): Builder
    {
        if ($user->mohDepartment && $user->mohDepartment()->active()->doesntExist()) {
            return $query->whereRaw('0 = 1');
        }

        $query->where('training_type', Application::PRACTICE);
        if ($user->mohDepartment) {
            return $query->whereHas('section', function ($q) use ($user) {
                $q->whereHas('departments', fn($dq) => $dq->where('departments.id', $user->mohDepartment->id)->visible());
            });
        }
        return $query->whereHas('section', function ($q) {
            $q->whereHas('departments', fn($dq) => $dq->visible());
        });
    }

    private function applyDepartmentHeadQuery(Builder $query, User $user): Builder
    {
        if ($user->department()->active()->exists()) {
            return $query->whereHas('section.departments', function ($q) use ($user) {
                $q->where('departments.id', $user->department->id)->visible();
            });
        }
        return $query->whereRaw('0 = 1');
    }

    private function applySectionHeadQuery(Builder $query, User $user): Builder
    {
        if ($user->section()->active()->exists()) {
            return $query->where('section_id', $user->section->id);
        }
        return $query->whereRaw('0 = 1');
    }
}

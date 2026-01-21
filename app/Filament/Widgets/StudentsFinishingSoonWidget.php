<?php

namespace App\Filament\Widgets;

use App\Models\User;

use App\Helpers\Constants;
use App\Models\Application;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageTable;
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

    protected static ?string $heading = 'متابعة المتدربين المنتهين قريباً';
    protected static bool $collapsible = false;

    public function getHeading(): string | Heading
    {
        $count = $this->table(app(\Filament\Tables\Table::class))->getQuery()->count();
        return 'متابعة المتدربين المنتهين قريباً (' . $count . ')';
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        // Allowed for: GTM, Admin, HOA, HOM, Department Head, Section Head
        // Hidden from: MOH (4), College Supervisor (5)
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
                Application::query()
                    ->where('status', Application::STATUS_STARTED_TRAINING)
                    ->where('end_date', '>=', Carbon::today())
                    ->where('end_date', '>=', Carbon::today())
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
                    if ($user->Section) {
                        return $query->where('section_id', $user->Section->id);
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
                    ->state(function (Application $record) {
                        $days = Carbon::today()->diffInDays($record->end_date, false);
                        return $days <= 0 ? 'ينتهي اليوم' : $days . ' أيام';
                    })
                    ->badge()
                    ->color(fn($state) => $state === 'ينتهي اليوم' ? 'danger' : 'warning'),
            ])
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
                    ->url(fn(Application $record): string => \App\Filament\Resources\Applications\ApplicationResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('لا يوجد طلاب تنتهي فترة تدريبهم قريباً');
    }
}

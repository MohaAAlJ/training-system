<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use App\Models\Department;
use App\Models\Section;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DepartmentTraineeCountWidget extends BaseWidget implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.widgets.department-trainee-count-widget';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public static function canView(): bool
    {
        if (request()->routeIs('filament.home.pages.dashboard')) {
            return false;
        }

        return (bool) Auth::user();
    }

    // =========================================================================
    // MODE & DYNAMIC LABELS
    // =========================================================================

    /**
     * 'department' → grouped by Department (Admin, GTM, Monitor, HOS)
     * 'section'    → grouped by Section    (HOA, HOM, HOD, College)
     * 'application'→ individual rows       (MOH)
     */
    public function getMode(): string
    {
        $user = Auth::user();

        return match (true) {
            $user->isAdmin() || $user->isTrainingManagerLike() || $user->isMonitor() => 'department',
            $user->isAdministrative() || $user->isMedicalManager() || $user->isDepartment() || $user->isCollegeSupervisor() || $user->isSectionHead() || $user->isMinistry() => 'section',
            default => 'department',
        };
    }

    public function getWidgetHeading(): string
    {
        return match ($this->getMode()) {
            'section'     => 'عدد الطلبات في الأقسام',
            'application' => 'طلبات تدريب الامتياز',
            default       => 'عدد الطلبات في الدوائر',
        };
    }

    public function getWidgetDescription(): string
    {
        return match ($this->getMode()) {
            'section'     => 'اختر الفترة الزمنية لعرض توزيع الطلبات على الأقسام.',
            'application' => 'اختر الفترة الزمنية لعرض طلبات تدريب الامتياز.',
            default       => 'اختر الفترة الزمنية لعرض توزيع الطلبات على الدوائر.',
        };
    }

    // =========================================================================
    // FORM
    // =========================================================================

    public function form(Schema $form): Schema
    {
        return $form
            ->statePath('data')
            ->schema([
                DatePicker::make('date_from')
                    ->label(fn(Get $get): string => $this->getDateFromLabel($get('status')))
                    ->native(false)
                    ->displayFormat('Y/m/d')
                    ->maxDate(fn(Get $get) => $get('date_to') ?: null)
                    ->live(),

                DatePicker::make('date_to')
                    ->label(fn(Get $get): string => $this->getDateToLabel($get('status')))
                    ->native(false)
                    ->displayFormat('Y/m/d')
                    ->minDate(fn(Get $get) => $get('date_from') ?: null)
                    ->live(),

                Select::make('status')
                    ->label('الحالة')
                    ->options([
                        Application::STATUS_STARTED_TRAINING => Application::getStatusLabel(Application::STATUS_STARTED_TRAINING),
                        Application::STATUS_ENDED_TRAINING   => Application::getStatusLabel(Application::STATUS_ENDED_TRAINING),
                        Application::STATUS_CANCELLED        => Application::getStatusLabel(Application::STATUS_CANCELLED),
                    ])
                    ->placeholder('الكل')
                    ->live(),
            ])
            ->columns(3);
    }

    // =========================================================================
    // TABLE
    // =========================================================================

    public function table(Table $table): Table
    {
        return $table
            ->heading(null)
            ->emptyStateHeading('لا توجد بيانات')
            ->emptyStateDescription($this->getEmptyStateDescription())
            ->query(fn(): Builder => $this->buildQuery())
            ->columns($this->getTableColumns())
            ->paginated(false);
    }

    private function getEmptyStateDescription(): string
    {
        return match ($this->getMode()) {
            'section'     => 'اختر الفترة الزمنية لعرض عدد المتدربين في كل قسم.',
            'application' => 'اختر الفترة الزمنية لعرض طلبات التدريب.',
            default       => 'اختر الفترة الزمنية لعرض عدد المتدربين في كل دائرة.',
        };
    }

    protected function getTableColumns(): array
    {
        $mode = $this->getMode();
        $user = Auth::user();

        if ($mode === 'application') {
            return [
                Tables\Columns\TextColumn::make('trainee.full_name')
                    ->label('المتدرب')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('section.administrative.name')
                    ->label('الإدارة')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('section.name')
                    ->label('القسم')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn($state) => Application::getStatusLabel($state))
                    ->color(fn($state) => Application::getStatusColor($state)),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('تاريخ البداية')
                    ->date('Y/m/d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('تاريخ النهاية')
                    ->date('Y/m/d')
                    ->sortable(),
            ];
        }

        $isCollegeMode    = $mode === 'section' && $user->isCollegeSupervisor();
        $isMohMode        = $mode === 'section' && $user->isMinistry();
        $showDuplicates   = $mode === 'department' && ($user->isAdmin() || $user->isTrainingManagerLike() || $user->isMonitor());
        $nameLabel        = $mode === 'section' ? 'القسم' : 'الدائرة';

        $columns = [
            Tables\Columns\TextColumn::make('name')
                ->label($nameLabel)
                ->searchable()
                ->sortable(),

            // HOD: show which administrative unit each section belongs to
            Tables\Columns\TextColumn::make('administrative.name')
                ->label('الإدارة')
                ->searchable()
                ->sortable()
                ->visible($mode === 'section' && ($user->isDepartment() || $user->isSectionHead() || $user->isCollegeSupervisor() || $user->isMinistry())),

            Tables\Columns\TextColumn::make('application_count')
                ->label('عدد الطلبات')
                ->numeric()
                ->sortable()
                ->badge()
                ->color(fn(int $state): string => $state > 0 ? 'success' : 'gray')
                ->alignCenter()
                ->formatStateUsing(fn($state) => $this->arabicToEnglish($state)),
        ];

        // Both breakdown columns hidden for College and MOH (total count is sufficient)
        if (! $isCollegeMode && ! $isMohMode) {
            $columns[] = Tables\Columns\TextColumn::make('university_count')
                ->label('تدريب جامعي')
                ->numeric()
                ->sortable()
                ->badge()
                ->color(fn(int $state): string => $state > 0 ? 'info' : 'gray')
                ->alignCenter()
                ->formatStateUsing(fn($state) => $this->arabicToEnglish($state));

            $columns[] = Tables\Columns\TextColumn::make('practice_count')
                ->label('تدريب امتياز')
                ->numeric()
                ->sortable()
                ->badge()
                ->color(fn(int $state): string => $state > 0 ? 'warning' : 'gray')
                ->alignCenter()
                ->formatStateUsing(fn($state) => $this->arabicToEnglish($state));
        }

        // Duplicate count: only meaningful for Admin/GTM/Monitor in department mode
        if ($showDuplicates) {
            $columns[] = Tables\Columns\TextColumn::make('duplicate_count')
                ->label('مكرر في أكثر من دائرة')
                ->numeric()
                ->sortable()
                ->badge()
                ->color(fn(int $state): string => $state > 0 ? 'warning' : 'gray')
                ->alignCenter()
                ->formatStateUsing(fn($state) => $this->arabicToEnglish($state))
                ->tooltip('عدد الطلبات المحسوبة أيضاً في دائرة أخرى');
        }

        return $columns;
    }

    // =========================================================================
    // SUMMARY COUNTS (banner)
    // =========================================================================

    public function isReady(): bool
    {
        return ! empty($this->data['date_from']) && ! empty($this->data['date_to']);
    }

    public function getTotalCount(): int
    {
        [$dateFrom, $dateTo, $status] = $this->getFilters();

        if (! $dateFrom || ! $dateTo) {
            return 0;
        }

        return $this->baseCountQuery($dateFrom, $dateTo, $status)->count();
    }

    public function getFormattedTotalCount(): string
    {
        return $this->arabicToEnglish($this->getTotalCount());
    }

    public function getTotalByType(int $type): int
    {
        [$dateFrom, $dateTo, $status] = $this->getFilters();

        if (! $dateFrom || ! $dateTo) {
            return 0;
        }

        return $this->baseCountQuery($dateFrom, $dateTo, $status)
            ->where('training_type', $type)
            ->count();
    }

    public function getFormattedUniversityCount(): string
    {
        return $this->arabicToEnglish($this->getTotalByType(Application::UNIVERSITY));
    }

    public function getFormattedPracticeCount(): string
    {
        return $this->arabicToEnglish($this->getTotalByType(Application::PRACTICE));
    }

    public function getCancelledCount(): int
    {
        [$dateFrom, $dateTo] = $this->getFilters();

        if (! $dateFrom || ! $dateTo) {
            return 0;
        }

        return Application::query()
            ->forUser(Auth::user())
            ->where('status', Application::STATUS_CANCELLED)
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->whereExists($this->departmentExistsSubQuery())
            ->count();
    }

    public function getFormattedCancelledCount(): string
    {
        return $this->arabicToEnglish($this->getCancelledCount());
    }

    public function hasStatus(): bool
    {
        return ! empty($this->data['status']);
    }

    public function showUniversityCount(): bool
    {
        $user = Auth::user();
        return ! $user->isMinistry() && ! $user->isCollegeSupervisor();
    }

    public function showPracticeCount(): bool
    {
        $user = Auth::user();
        return ! $user->isCollegeSupervisor() && ! $user->isMinistry();
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function getFilters(): array
    {
        return [
            $this->data['date_from'] ?? null,
            $this->data['date_to']   ?? null,
            $this->data['status']    ?? null,
        ];
    }

    private function getDateColumn(?int $status): string
    {
        if (! $status) {
            return 'created_at';
        }

        return $status === Application::STATUS_ENDED_TRAINING ? 'end_date' : 'start_date';
    }

    private function getDateFromLabel(?int $status): string
    {
        return match ((int) $status) {
            Application::STATUS_ENDED_TRAINING   => 'من تاريخ نهاية التدريب',
            Application::STATUS_STARTED_TRAINING,
            Application::STATUS_CANCELLED        => 'من تاريخ بداية التدريب',
            default                              => 'من تاريخ الإنشاء',
        };
    }

    private function getDateToLabel(?int $status): string
    {
        return match ((int) $status) {
            Application::STATUS_ENDED_TRAINING   => 'إلى تاريخ نهاية التدريب',
            Application::STATUS_STARTED_TRAINING,
            Application::STATUS_CANCELLED        => 'إلى تاريخ بداية التدريب',
            default                              => 'إلى تاريخ الإنشاء',
        };
    }

    private function baseCountQuery(string $dateFrom, string $dateTo, ?int $status): Builder
    {
        $dateCol = $this->getDateColumn($status);

        $query = Application::query()
            ->forUser(Auth::user())
            ->whereDate($dateCol, '>=', $dateFrom)
            ->whereDate($dateCol, '<=', $dateTo)
            ->whereExists($this->departmentExistsSubQuery());

        if ($status) {
            $query->where('status', $status);
        }

        return $query;
    }

    private function departmentExistsSubQuery(): \Illuminate\Database\Query\Builder
    {
        return Department::query()
            ->active()
            ->visible()
            ->join('department_section as ds', 'ds.department_id', '=', 'departments.id')
            ->whereColumn('ds.section_id', 'applications.section_id')
            ->selectRaw('1')
            ->toBase();
    }

    private function arabicToEnglish($number): string
    {
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($arabic, $english, (string) $number);
    }

    // =========================================================================
    // TABLE QUERY
    // =========================================================================

    private function buildQuery(): Builder
    {
        return match ($this->getMode()) {
            'section'     => $this->buildSectionQuery(),
            'application' => $this->buildApplicationQuery(),
            default       => $this->buildDepartmentQuery(),
        };
    }

    // -------------------------------------------------------------------------
    // Department mode — Admin / GTM / Monitor / HOS
    // -------------------------------------------------------------------------
    private function buildDepartmentQuery(): Builder
    {
        [$dateFrom, $dateTo, $status] = $this->getFilters();
        $user = Auth::user();

        $base = Department::query()->active()->visible();

        // HOS: limit to departments whose sections include their own section
        if ($user->isSectionHead() && $user->section) {
            $base->whereHas('sections', fn($q) => $q->where('sections.id', $user->section->id));
        }

        if ($user->isAssistantTrainingManager()) {
            $base->whereIn('departments.id', $user->managedDepartmentIds());
        }

        if (! $dateFrom || ! $dateTo) {
            return $base
                ->selectRaw('departments.*, 0 as application_count, 0 as university_count, 0 as practice_count')
                ->whereRaw('0 = 1');
        }

        $dateCol = $this->getDateColumn($status);

        $makeSubQuery = function (?int $typeFilter = null) use ($dateFrom, $dateTo, $dateCol, $status, $user) {
            $q = Application::query()
                ->selectRaw('COUNT(*)')
                ->forUser($user)
                ->join('department_section', 'applications.section_id', '=', 'department_section.section_id')
                ->whereColumn('department_section.department_id', 'departments.id')
                ->whereDate("applications.{$dateCol}", '>=', $dateFrom)
                ->whereDate("applications.{$dateCol}", '<=', $dateTo);

            if ($status) {
                $q->where('applications.status', $status);
            }

            if ($typeFilter !== null) {
                $q->where('applications.training_type', $typeFilter);
            }

            return $q;
        };

        $select = [
            'departments.*',
            'application_count' => $makeSubQuery(),
            'university_count'  => $makeSubQuery(Application::UNIVERSITY),
            'practice_count'    => $makeSubQuery(Application::PRACTICE),
        ];

        // Duplicate count only for Admin / GTM / Monitor
        if ($user->isAdmin() || $user->isTrainingManagerLike() || $user->isMonitor()) {
            $select['duplicate_count'] = $makeSubQuery()
                ->whereExists(
                    Department::query()
                        ->active()
                        ->visible()
                        ->join('department_section as ds2', 'ds2.department_id', '=', 'departments.id')
                        ->whereColumn('ds2.section_id', 'applications.section_id')
                        ->whereColumn('departments.id', '!=', 'department_section.department_id')
                        ->selectRaw('1')
                        ->toBase()
                );
        }

        return $base
            ->addSelect($select)
            ->orderByDesc('application_count');
    }

    // -------------------------------------------------------------------------
    // Section mode — HOA / HOM / HOD / HOS / College / MOH
    // -------------------------------------------------------------------------
    private function buildSectionQuery(): Builder
    {
        [$dateFrom, $dateTo, $status] = $this->getFilters();
        $user = Auth::user();

        $mohStatuses = [
            Application::STATUS_NEW,
            Application::STATUS_INITIAL_APPROVE,
            Application::STATUS_CONFIRMATION,
            Application::STATUS_WAITING_LIST,
            Application::STATUS_STARTED_TRAINING,
            Application::STATUS_ENDED_TRAINING,
            Application::STATUS_REJECTED,
        ];

        $base = match (true) {
            $user->isAdministrative() && $user->administrative !== null =>
            Section::query()->active()->where('administrative_id', $user->administrative->id),

            $user->isMedicalManager() && $user->administrativeMedicalHead !== null =>
            Section::query()->active()
                ->where('administrative_id', $user->administrativeMedicalHead->id)
                ->whereHas('departments', fn($d) => $d->where('is_medical', true)->visible()),

            $user->isDepartment() && $user->department !== null =>
            Section::query()->active()
                ->whereHas('departments', fn($d) => $d->where('departments.id', $user->department->id)->visible()),

            $user->isCollegeSupervisor() && $user->college !== null =>
            Section::query()->active()
                ->whereHas(
                    'applications',
                    fn($q) => $q
                        ->where('training_type', Application::UNIVERSITY)
                        ->where('college_id', $user->college->id)
                ),

            $user->isSectionHead() && $user->section !== null =>
            Section::query()->active()->where('sections.id', $user->section->id),

            $user->isMinistry() =>
            $user->mohDepartment && $user->mohDepartment()->active()->exists()
                ? Section::query()->active()
                ->whereHas('departments', fn($d) => $d->where('departments.id', $user->mohDepartment->id)->visible())
                : Section::query()->active()
                ->whereHas('applications', fn($q) => $q->where('training_type', Application::PRACTICE)),

            default => Section::query()->whereRaw('0 = 1'),
        };

        if (! $dateFrom || ! $dateTo) {
            return $base
                ->selectRaw('sections.*, 0 as application_count, 0 as university_count, 0 as practice_count')
                ->whereRaw('0 = 1');
        }

        $isCollege = $user->isCollegeSupervisor();
        $isMoh     = $user->isMinistry();
        $dateCol   = $this->getDateColumn($status);
        $college   = $isCollege ? $user->college : null;

        $makeSubQuery = function (?int $typeFilter = null) use ($dateFrom, $dateTo, $dateCol, $status, $mohStatuses, $isCollege, $isMoh, $college) {
            $q = Application::query()
                ->selectRaw('COUNT(*)')
                ->whereColumn('applications.section_id', 'sections.id')
                ->whereDate("applications.{$dateCol}", '>=', $dateFrom)
                ->whereDate("applications.{$dateCol}", '<=', $dateTo);

            if ($status) {
                $q->where('applications.status', $status);
            }

            if ($typeFilter !== null) {
                $q->where('applications.training_type', $typeFilter);
            }

            if ($isCollege && $college) {
                $q->where('applications.training_type', Application::UNIVERSITY)
                    ->where('applications.college_id', $college->id);
            } elseif ($isMoh) {
                $q->where('applications.training_type', Application::PRACTICE);
                if (! $status) {
                    $q->whereIn('applications.status', $mohStatuses);
                }
            }
            // HOA / HOM / HOD / HOS: no additional restriction — $status filter above handles it

            return $q;
        };

        $query = $base
            ->addSelect([
                'sections.*',
                'application_count' => $makeSubQuery(),
                'university_count'  => $makeSubQuery(Application::UNIVERSITY),
                'practice_count'    => $makeSubQuery(Application::PRACTICE),
            ])
            ->orderByDesc('application_count');

        // Eager-load administrative for roles that show the column
        if ($user->isDepartment() || $user->isSectionHead() || $user->isCollegeSupervisor() || $user->isMinistry()) {
            $query->with('administrative');
        }

        return $query;
    }

    // -------------------------------------------------------------------------
    // Application mode — MOH
    // -------------------------------------------------------------------------
    private function buildApplicationQuery(): Builder
    {
        [$dateFrom, $dateTo, $status] = $this->getFilters();
        $user = Auth::user();

        $query = Application::query()->forUser($user);

        if (! $dateFrom || ! $dateTo) {
            return $query->whereRaw('0 = 1');
        }

        $dateCol = $this->getDateColumn($status);

        $query->whereDate($dateCol, '>=', $dateFrom)
            ->whereDate($dateCol, '<=', $dateTo);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->with(['trainee', 'section.administrative'])->orderByDesc('created_at');
    }
}

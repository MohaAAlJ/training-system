<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class GTMRecentApplications extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public static ?string $heading = 'تحتاج إجراءات';

    public function getHeading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return '';
    }

    /**
     * Auto-collapse the widget when there are no pending applications.
     * Overrides any user preference to keep it expanded.
     */
    public function isCollapsed(): bool
    {
        return $this->isEmpty();
    }

    /**
     * Check whether the table query returns zero records.
     */
    protected function isEmpty(): bool
    {
        return $this->getTableQuery()->doesntExist();
    }




    public static function canView(): bool
    {
        // Hide from the dashboard (where Filament auto-discovers widgets)
        // The Stats page registers this widget manually and has its own canView() guard
        if (request()->routeIs('filament.home.pages.dashboard')) {
            return false;
        }

        $user = Auth::user();
        if (!$user) return false;

        // Visible for: GTM, Admin, Monitor, College Supervisor, and MOH
        return in_array($user->role, [
            User::ROLE_GTM,
            User::ROLE_ASSISTANT_TRAINING_MANAGER,
            User::ROLE_ADMIN,
            User::ROLE_MONITOR,
            User::ROLE_COLLEGE,
            User::ROLE_MOH,
        ]);
    }

    public static function updateCalculatedEndDate($get, $set): void
    {
        $hours       = (int) $get('training_hours');
        $dailyHrs    = (int) $get('daily_hours');
        $days        = (array) $get('training_days');
        $days        = array_filter($days, fn($v) => $v !== '' && $v !== null);
        $daysPerWeek = count($days);
        $startDate   = $get('start_date');

        if ($hours > 0 && $dailyHrs > 0 && $daysPerWeek > 0 && $startDate) {
            $sessions = (int) ceil($hours / $dailyHrs);
            $calendarDaysToAdd = (int) round(($sessions / $daysPerWeek) * 7);
            $daysToJump = max(0, $calendarDaysToAdd - 1);
            $auto = \Carbon\Carbon::parse($startDate)->addDays($daysToJump)->format('Y-m-d');
            $set('end_date', $auto);
        }
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
                    ->forUser(Auth::user())
                    ->whereIn('status', [
                        Application::STATUS_NEW,
                        Application::STATUS_INITIAL_APPROVE,
                        Application::STATUS_CONFIRMATION,
                        Application::STATUS_WAITING_LIST
                    ])
                    ->orderBy('updated_at', 'asc')
                    ->with([
                        'trainee',
                        'institution',
                        'major',
                        'section.administrative',
                        'section.departments' => fn($q) => $q->visible()
                    ])
            )
            ->modifyQueryUsing(function ($query) {
                $user = Auth::user();

                return match (true) {
                    $user->isAdmin() || $user->isTrainingManagerLike() || $user->isMonitor() => $this->applyAdminOrGtmQuery($query, $user),
                    $user->isCollegeSupervisor() => $this->applyCollegeSupervisorQuery($query, $user),
                    $user->isMinistry() => $this->applyMohQuery($query, $user),
                    default => $query,
                };
            })
            ->columns([
                Tables\Columns\TextColumn::make('trainee.full_name')
                    ->label('المتدرب')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('trainee.national_id')
                    ->label('رقم الهوية')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('institution.name')
                    ->label('المؤسسة')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->formatStateUsing(fn($state, $record) => $record->training_type === Application::PRACTICE ? '' : $state)
                    ->visible(fn() => Auth::check() && (
                        Auth::user()->isAdmin() ||
                        Auth::user()->isTrainingManagerLike()
                    ))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('major.name')
                    ->label('التخصص')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->formatStateUsing(fn($state, $record) => $record->training_type === Application::PRACTICE ? '' : $state)
                    ->visible(fn() => Auth::check() && (
                        Auth::user()->isAdmin() ||
                        Auth::user()->isTrainingManagerLike() ||
                        Auth::user()->isCollegeSupervisor()
                    ))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('training_type')
                    ->label('نوع التدريب')
                    ->badge()
                    ->formatStateUsing(fn($state) => Application::getTrainingTypeLabel((int)$state))
                    ->color(fn($state) => Application::getTrainingTypeColor((int)$state))
                    ->toggleable()
                    ->visible(fn() => !Auth::user()->isMinistry())
                    ->sortable(),
                Tables\Columns\TextColumn::make('section.administrative.name')
                    ->label('الإدارة')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('section.departments.name')
                    ->label('الدائرة')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('section.name')
                    ->label('القسم')
                    ->description(fn($record) => $record->section?->administrative?->name ?? '-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->sortable()
                    ->badge()
                    ->color(fn($state): string => Application::getStatusColor((int)$state))
                    ->formatStateUsing(fn($state): string => (function ($state) {
                        $actualState = $state;
                        $key = 'translation.status.' . $actualState;
                        $translated = \Illuminate\Support\Facades\Lang::get($key, [], 'ar');
                        return $translated === $key ? $actualState : $translated;
                    })($state)),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التقديم')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordUrl(
                fn(Application $record): string => \App\Filament\Resources\Applications\ApplicationResource::getUrl('view', ['record' => $record->id]),
            )
            ->actions([
                ViewAction::make()
                    ->form(fn($form) => \App\Filament\Resources\Applications\Schemas\ApplicationForm::configure($form)),

                Action::make('initial_approve')
                    ->label('موافقة مبدئية')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn($record) => Auth::user()->isTrainingManagerLike() && $record->status == Application::STATUS_NEW)
                    ->requiresConfirmation()
                    ->successNotificationTitle('تمت الموافقة المبدئية بنجاح')
                    ->action(fn($record) => $record->update(['status' => Application::STATUS_INITIAL_APPROVE])),

                Action::make('confirm')
                    ->label('تأكيد')
                    ->color('success')
                    ->icon('heroicon-o-check-badge')
                    ->visible(
                        fn($record) =>
                        $record->status === Application::STATUS_INITIAL_APPROVE &&
                            (Auth::user()->isAdmin() || Auth::user()->isCollegeSupervisor() || Auth::user()->isMinistry())
                    )
                    ->requiresConfirmation()
                    ->successNotificationTitle('تم تأكيد الطلب بنجاح')
                    ->action(fn($record) => $record->update([
                        'status' => Application::STATUS_CONFIRMATION,
                        'accepted_at' => now(),
                    ])),

                Action::make('process_confirmation')
                    ->label('معالجة التأكيد')
                    ->color('success')
                    ->icon('heroicon-o-play')
                    ->visible(fn($record) => Auth::user()->isTrainingManagerLike() && $record->status == Application::STATUS_CONFIRMATION)
                    ->form([
                        \Filament\Forms\Components\Select::make('new_status')
                            ->label('الحالة الجديدة')
                            ->options([
                                Application::STATUS_WAITING_LIST => 'قائمة الانتظار',
                                Application::STATUS_STARTED_TRAINING => 'بدء التدريب',
                            ])
                            ->required()
                            ->reactive()
                            ->default(Application::STATUS_STARTED_TRAINING),
                        DatePicker::make('start_date')
                            ->label('تاريخ البدء')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->format('Y-m-d')
                            ->displayFormat('Y/m/d')
                            ->reactive()
                            ->visible(fn($get) => $get('new_status') && (int)$get('new_status') === Application::STATUS_STARTED_TRAINING)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateCalculatedEndDate($get, $set);
                            }),
                        TextInput::make('training_hours')
                            ->label('إجمالي ساعات التدريب')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->suffix('ساعة')
                            ->visible(fn($get) => $get('new_status') && (int)$get('new_status') === Application::STATUS_STARTED_TRAINING)
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateCalculatedEndDate($get, $set);
                            }),
                        \Filament\Forms\Components\CheckboxList::make('training_days')
                            ->label('أيام التدريب')
                            ->options(Application::ALL_DAYS)
                            ->columns(5)
                            ->required()
                            ->visible(fn($get) => $get('new_status') && (int)$get('new_status') === Application::STATUS_STARTED_TRAINING)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateCalculatedEndDate($get, $set);
                            }),
                        TextInput::make('daily_hours')
                            ->label('ساعات باليوم')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(6)
                            ->visible(fn($get) => $get('new_status') && (int)$get('new_status') === Application::STATUS_STARTED_TRAINING)
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateCalculatedEndDate($get, $set);
                            }),
                        DatePicker::make('end_date')
                            ->label('تاريخ الانتهاء المتوقع')
                            ->required()
                            ->visible(fn($get) => $get('new_status') && (int)$get('new_status') === Application::STATUS_STARTED_TRAINING)
                            ->default(now()->addDays(30)->toDateString())
                            ->reactive(),
                    ])
                    ->successNotificationTitle('تمت معالجة التأكيد بنجاح')
                    ->action(function ($record, array $data) {
                        $newStatus = (int)$data['new_status'];

                        if ($newStatus === Application::STATUS_STARTED_TRAINING) {
                            $hours       = (int) ($data['training_hours'] ?? 0);
                            $dailyHrs    = (int) ($data['daily_hours'] ?? 6);
                            $selectedDays = array_filter((array) ($data['training_days'] ?? []), fn($v) => $v !== '' && $v !== null);

                            $record->update([
                                'status' => Application::STATUS_STARTED_TRAINING,
                                'start_date' => $data['start_date'],
                                'end_date' => $data['end_date'],
                                'training_hours' => $hours ?: null,
                                'days_note' => [
                                    'training_days' => array_map('intval', $selectedDays),
                                    'daily_hours'   => $dailyHrs,
                                    'note'          => null,
                                ],
                            ]);
                        } else {
                            $record->update([
                                'status' => Application::STATUS_WAITING_LIST,
                            ]);
                        }
                    }),

                Action::make('begin_training_from_waiting')
                    ->label('بدء التدريب')
                    ->color('success')
                    ->icon('heroicon-o-play-circle')
                    ->visible(fn($record) => Auth::user()->isTrainingManagerLike() && $record->status == Application::STATUS_WAITING_LIST)
                    ->form([
                        DatePicker::make('start_date')
                            ->label('تاريخ البدء')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->format('Y-m-d')
                            ->displayFormat('Y/m/d')
                            ->reactive()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateCalculatedEndDate($get, $set);
                            }),
                        TextInput::make('training_hours')
                            ->label('إجمالي ساعات التدريب')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->suffix('ساعة')
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateCalculatedEndDate($get, $set);
                            }),
                        \Filament\Forms\Components\CheckboxList::make('training_days')
                            ->label('أيام التدريب')
                            ->options(Application::ALL_DAYS)
                            ->columns(5)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateCalculatedEndDate($get, $set);
                            }),
                        TextInput::make('daily_hours')
                            ->label('ساعات باليوم')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(6)
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateCalculatedEndDate($get, $set);
                            }),
                        DatePicker::make('end_date')
                            ->label('تاريخ الانتهاء المتوقع')
                            ->required()
                            ->default(now()->addDays(30)->toDateString())
                            ->reactive(),
                    ])
                    ->successNotificationTitle('تم بدء التدريب بنجاح')
                    ->action(function ($record, array $data) {
                        $hours       = (int) ($data['training_hours'] ?? 0);
                        $dailyHrs    = (int) ($data['daily_hours'] ?? 6);
                        $selectedDays = array_filter((array) ($data['training_days'] ?? []), fn($v) => $v !== '' && $v !== null);

                        $record->update([
                            'status' => Application::STATUS_STARTED_TRAINING,
                            'start_date' => $data['start_date'],
                            'end_date' => $data['end_date'],
                            'training_hours' => $hours ?: null,
                            'days_note' => [
                                'training_days' => array_map('intval', $selectedDays),
                                'daily_hours'   => $dailyHrs,
                                'note'          => null,
                            ],
                        ]);
                    }),

                DeleteAction::make()
                    ->label('رفض')
                    ->modalHeading('رفض الطلب')
                    ->modalDescription('هل أنت متأكد من رفض هذا الطلب؟ سيتم نقله إلى قائمة المرفوضات.')
                    ->visible(fn($record) => !$record->trashed() && (Auth::user()->isTrainingManagerLike()))
                    ->action(function ($record) {
                        $record->update(['status' => Application::STATUS_REJECTED]);
                        $record->delete();
                    }),
            ]);
    }

    private function applyAdminOrGtmQuery($query, User $user)
    {
        // GTM and Admin see New (1), Confirmation (3), and Waiting (4)
        // They don't typically act on Initial Approve (2) as that's for MOH/College
        return $query->forUser($user)->whereIn('status', [
            Application::STATUS_NEW,
            Application::STATUS_CONFIRMATION,
            Application::STATUS_WAITING_LIST
        ]);
    }

    private function applyCollegeSupervisorQuery($query, User $user)
    {
        $college = $user->college;
        if (!$college || $user->college()->active()->doesntExist()) {
            return $query->whereRaw('0 = 1');
        }

        $collegeId = $college->id;
        // College Supervisors MUST see Initial Approve (2) to confirm
        return $query->where('status', Application::STATUS_INITIAL_APPROVE)
            ->where('training_type', Application::UNIVERSITY)
            ->where('college_id', $collegeId);
    }

    private function applyMohQuery($query, User $user)
    {
        if ($user->mohDepartment && $user->mohDepartment()->active()->doesntExist()) {
            return $query->whereRaw('0 = 1');
        }

        // MOH sees Initial Approve (2) for practice training
        // If MOH has connected department, filter by that department only
        $query = $query->where('status', Application::STATUS_INITIAL_APPROVE)
            ->where('training_type', Application::PRACTICE);

        if ($user->mohDepartment) {
            return $query->whereHas('section', fn($q) => $q->whereHas('departments', fn($dq) => $dq->where('departments.id', $user->mohDepartment->id)->visible()));
        }

        // Unlinked MOH - show only visible departments
        return $query->whereHas('section', fn($q) => $q->whereHas('departments', fn($dq) => $dq->visible()));
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\User;

use App\Helpers\Constants;
use App\Models\Application;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Lang;

class GTMRecentApplications extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'تحتاج إجراءات';

    public static function canView(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        // Visible for: GTM, Admin, College Supervisor, and MOH
        return in_array($user->role, [
            User::ROLE_GTM,
            User::ROLE_ADMIN,
            User::ROLE_COLLEGE,
            User::ROLE_MOH,
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Application::query()
                    ->whereIn('status', [
                        Application::STATUS_NEW,
                        Application::STATUS_INITIAL_APPROVE,
                        Application::STATUS_CONFIRMATION,
                        Application::STATUS_WAITING_LIST
                    ])
                    ->latest('created_at')
            )
            ->modifyQueryUsing(function ($query) {
                $user = Auth::user();

                if ($user->isAdmin() || $user->isGeneralTrainingManager()) {
                    // GTM and Admin see New (1), Confirmation (3), and Waiting (4)
                    // They don't typically act on Initial Approve (2) as that's for MOH/College
                    return $query->whereIn('status', [
                        Application::STATUS_NEW,
                        Application::STATUS_CONFIRMATION,
                        Application::STATUS_WAITING_LIST
                    ]);
                }

                if ($user->isCollegeSupervisor()) {
                    $collegeId = $user->College?->id;
                    // College Supervisors MUST see Initial Approve (2) to confirm
                    return $query->where('status', Application::STATUS_INITIAL_APPROVE)
                        ->where('training_type', Application::TRAINING_TYPE_UNIVERSITY)
                        ->whereHas('trainee', function ($q) use ($collegeId) {
                            $q->where('college_id', $collegeId);
                        });
                }

                if ($user->isMinistry()) {
                    // MOH MUST see Initial Approve (2) to confirm
                    return $query->where('status', Application::STATUS_INITIAL_APPROVE)
                        ->where('training_type', Application::TRAINING_TYPE_PRACTICE);
                }

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('trainee.full_name')
                    ->label('المتدرب')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('trainee.national_id')
                    ->label('رقم الهوية')
                    ->searchable(),
                Tables\Columns\TextColumn::make('trainee.institution.name')
                    ->label('المؤسسة')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->formatStateUsing(fn($state, $record) => $record->training_type === Application::TRAINING_TYPE_PRACTICE ? '' : $state)
                    ->visible(fn() => Auth::check() && (
                        Auth::user()->isAdmin() ||
                        Auth::user()->isGeneralTrainingManager()
                    )),
                Tables\Columns\TextColumn::make('trainee.major.name')
                    ->label('التخصص')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->formatStateUsing(fn($state, $record) => $record->training_type === Application::TRAINING_TYPE_PRACTICE ? '' : $state)
                    ->visible(fn() => Auth::check() && (
                        Auth::user()->isAdmin() ||
                        Auth::user()->isGeneralTrainingManager() ||
                        Auth::user()->isCollegeSupervisor()
                    )),
                Tables\Columns\TextColumn::make('training_type_label')
                    ->label('نوع التدريب')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'تدريب جامعي' => 'info',
                        'مزاولة مهنة' => 'success',
                        default => 'gray',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('administrative.title')
                    ->label('الإدارة')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('department.title')
                    ->label('الدائرة')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('section.name_location')
                    ->label('القسم')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->sortable()
                    ->badge()
                    ->color(fn($state): string => match ((int)$state) {
                        1 => 'info',
                        2 => 'primary',
                        3 => 'primary',
                        4 => 'warning',
                        5 => 'success',
                        6 => 'gray',
                        7 => 'danger',
                        8 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state): string => (function ($state) {
                        $key = 'translation.status.' . $state;
                        $translated = \Illuminate\Support\Facades\Lang::get($key, [], 'ar');
                        return $translated === $key ? $state : $translated;
                    })($state)),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التقديم')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                ViewAction::make(),

                Action::make('initial_approve')
                    ->label('موافقة مبدئية')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn($record) => Auth::user()->isGeneralTrainingManager() && $record->status == Application::STATUS_NEW)
                    ->requiresConfirmation()
                    ->successNotificationTitle('تمت الموافقة المبدئية بنجاح')
                    ->action(fn($record) => $record->update(['status' => Application::STATUS_INITIAL_APPROVE])),

                Action::make('confirm')
                    ->label('تأكيد')
                    ->color('success')
                    ->icon('heroicon-o-check-badge')
                    ->visible(
                        fn($record) =>
                        (int)$record->status === Application::STATUS_INITIAL_APPROVE &&
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
                    ->visible(fn($record) => Auth::user()->isGeneralTrainingManager() && $record->status == Application::STATUS_CONFIRMATION)
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
                            ->format('Y/m/d')
                            ->displayFormat('Y/m/d')
                            ->reactive()
                            ->visible(fn($get) => (int)$get('new_status') === Application::STATUS_STARTED_TRAINING),
                        TextInput::make('duration')
                            ->label('المدة (يوم)')
                            ->numeric()
                            ->required()
                            ->default(30)
                            ->reactive()
                            ->visible(fn($get) => (int)$get('new_status') === Application::STATUS_STARTED_TRAINING),
                        \Filament\Forms\Components\Placeholder::make('calculated_end_date')
                            ->label('تاريخ الانتهاء المتوقع')
                            ->content(function ($get) {
                                $startDate = $get('start_date');
                                $duration = $get('duration');

                                if ($startDate && $duration) {
                                    try {
                                        $start = \Carbon\Carbon::parse($startDate);
                                        $end = $start->copy()->addDays((int)$duration);
                                        return $end->format('Y-m-d') . ' (' . $end->translatedFormat('l، d F Y') . ')';
                                    } catch (\Exception $e) {
                                        return 'غير محدد';
                                    }
                                }
                                return 'غير محدد';
                            })
                            ->visible(fn($get) => (int)$get('new_status') === Application::STATUS_STARTED_TRAINING),
                    ])
                    ->successNotificationTitle('تمت معالجة التأكيد بنجاح')
                    ->action(function ($record, array $data) {
                        $newStatus = (int)$data['new_status'];

                        if ($newStatus === Application::STATUS_STARTED_TRAINING) {
                            $startDate = \Carbon\Carbon::parse($data['start_date']);
                            $duration = (int)$data['duration'];
                            $endDate = $startDate->copy()->addDays($duration);

                            $record->update([
                                'status' => Application::STATUS_STARTED_TRAINING,
                                'start_date' => $startDate,
                                'duration' => $duration,
                                'end_date' => $endDate,
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
                    ->visible(fn($record) => Auth::user()->isGeneralTrainingManager() && $record->status == Application::STATUS_WAITING_LIST)
                    ->form([
                        DatePicker::make('start_date')
                            ->label('تاريخ البدء')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->format('Y/m/d')
                            ->displayFormat('Y/m/d')
                            ->reactive(),
                        TextInput::make('duration')
                            ->label('المدة (يوم)')
                            ->numeric()
                            ->required()
                            ->default(30)
                            ->reactive(),
                        \Filament\Forms\Components\Placeholder::make('calculated_end_date')
                            ->label('تاريخ الانتهاء المتوقع')
                            ->content(function ($get) {
                                $startDate = $get('start_date');
                                $duration = $get('duration');

                                if ($startDate && $duration) {
                                    try {
                                        $start = \Carbon\Carbon::parse($startDate);
                                        $end = $start->copy()->addDays((int)$duration);
                                        return $end->format('Y-m-d') . ' (' . $end->translatedFormat('l، d F Y') . ')';
                                    } catch (\Exception $e) {
                                        return 'غير محدد';
                                    }
                                }
                                return 'غير محدد';
                            }),
                    ])
                    ->successNotificationTitle('تم بدء التدريب بنجاح')
                    ->action(function ($record, array $data) {
                        $startDate = \Carbon\Carbon::parse($data['start_date']);
                        $duration = (int)$data['duration'];
                        $endDate = $startDate->copy()->addDays($duration);

                        $record->update([
                            'status' => Application::STATUS_STARTED_TRAINING,
                            'start_date' => $startDate,
                            'duration' => $duration,
                            'end_date' => $endDate,
                        ]);
                    }),

                DeleteAction::make()
                    ->label('رفض')
                    ->modalHeading('رفض الطلب')
                    ->modalDescription('هل أنت متأكد من رفض هذا الطلب؟ سيتم نقله إلى قائمة المرفوضات.')
                    ->visible(fn($record) => !$record->trashed() && (Auth::user()->isAdmin() || Auth::user()->isGeneralTrainingManager()))
                    ->action(function ($record) {
                        $record->update(['status' => Application::STATUS_REJECTED]);
                        $record->delete();
                    }),
            ]);
    }
}

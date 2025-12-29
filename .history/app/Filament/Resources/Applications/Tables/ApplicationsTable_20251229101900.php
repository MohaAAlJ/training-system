<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Models\Application;
use App\Models\Section;
use App\Models\Department;
use App\Models\Administrative;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Auth;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('trainee.full_name')
                    ->label('المتدرب')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('trainee.national_id')
                    ->label('رقم الهوية')
                    ->searchable(),
                TextColumn::make('trainee.institution.name')
                    ->label('المؤسسة')
                    ->searchable(!Auth::user()->isCollegeSupervisor())
                    ->sortable(!Auth::user()->isCollegeSupervisor())
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->formatStateUsing(fn($state, $record) => $record->training_type === Application::TRAINING_TYPE_PRACTICE ? '' : $state)
                    ->visible(fn() => Auth::check() && (
                        Auth::user()->isAdmin() ||
                        Auth::user()->isDepartment() ||
                        Auth::user()->isHOA() ||
                        Auth::user()->isGeneralTrainingManager() ||
                        Auth::user()->isSectionHead()
                    )),
                TextColumn::make('trainee.major.name')
                    ->label('التخصص')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->formatStateUsing(fn($state, $record) => $record->training_type === Application::TRAINING_TYPE_PRACTICE ? '' : $state)
                    ->visible(fn() => Auth::check() && (
                        Auth::user()->isAdmin() ||
                        Auth::user()->isDepartment() ||
                        Auth::user()->isHOA() ||
                        Auth::user()->isGeneralTrainingManager() ||
                        Auth::user()->isCollegeSupervisor()
                    )),
                TextColumn::make('training_type_label')
                    ->label('نوع التدريب')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'تدريب جامعي' => 'info',
                        'مزاولة مهنة' => 'success',
                        default => 'gray',
                    })
                    ->toggleable()
                    ->visible(fn() => Auth::check() && (
                        Auth::user()->isAdmin() ||
                        Auth::user()->isDepartment() ||
                        Auth::user()->isHOA() ||
                        Auth::user()->isGeneralTrainingManager() ||
                        Auth::user()->isSectionHead()
                    ) && !Auth::user()->isMinistry()),
                TextColumn::make('administrative.title')
                    ->label('الإدارة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('department.title')
                    ->label('الدائرة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('section.name_location')
                    ->label('القسم')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('start_date')
                    ->label('تاريخ البدء')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('status')
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
                TextColumn::make('trainee.training_hours')
                    ->label('ساعات التدريب')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('accepted_at')
                    ->label('تاريخ القبول')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tags')
                    ->label('الوسوم')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(fn() => array_combine(
                        Application::STATUSES,
                        array_map(fn($s) => \Illuminate\Support\Facades\Lang::get("translation.status.$s", [], 'ar'), Application::STATUSES)
                    )),
                SelectFilter::make('training_type')
                    ->label('نوع التدريب')
                    ->options(Application::TRAINING_TYPES)
                    ->visible(fn() => Auth::check() && (
                        Auth::user()->isAdmin() ||
                        Auth::user()->isDepartment() ||
                        Auth::user()->isHOA() ||
                        Auth::user()->isGeneralTrainingManager()
                    )),
                SelectFilter::make('department_id')
                    ->label('القسم')
                    ->relationship('department', 'title')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('trainee_id')
                    ->label('المتدرب')
                    ->relationship('trainee', 'full_name')
                    ->searchable()
                    ->preload(),
                Filter::make('start_date')
                    ->label('نطاق تاريخ البدء')
                    ->form([
                        DatePicker::make('start_date_from')
                            ->label('من'),
                        DatePicker::make('start_date_to')
                            ->label('إلى'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['start_date_from'], fn(Builder $q) => $q->whereDate('start_date', '>=', $data['start_date_from']))
                            ->when($data['start_date_to'], fn(Builder $q) => $q->whereDate('start_date', '<=', $data['start_date_to']));
                    }),
            ])

            ->recordActions([
                ViewAction::make()
                    ->color('info')
                    ->outlined(),

                EditAction::make()
                    ->color('danger')
                    ->outlined(),

                // Group 1: Change Status Action (Visible only in 'all' tab)
                ActionGroup::make([
                    Action::make('to_new')
                        ->label('تحويل إلى جديد')
                        ->icon('heroicon-o-sparkles')
                        ->requiresConfirmation()
                        ->action(fn(Application $record) => $record->update(['status' => Application::STATUS_NEW]))
                        ->visible(fn(Application $record) => $record->status !== Application::STATUS_NEW),

                    Action::make('to_initial_approve')
                        ->label('موافقة مبدئية')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->action(fn(Application $record) => $record->update(['status' => Application::STATUS_INITIAL_APPROVE]))
                        ->visible(fn(Application $record) => $record->status !== Application::STATUS_INITIAL_APPROVE),

                    Action::make('to_confirmed')
                        ->label('تأكيد')
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->action(fn(Application $record) => $record->update(['status' => Application::STATUS_CONFIRMATION]))
                        ->visible(fn(Application $record) => $record->status !== Application::STATUS_CONFIRMATION),

                    Action::make('to_waiting_list')
                        ->label('قائمة الانتظار')
                        ->icon('heroicon-o-clock')
                        ->requiresConfirmation()
                        ->action(fn(Application $record) => $record->update(['status' => Application::STATUS_WAITING_LIST]))
                        ->visible(fn(Application $record) => $record->status !== Application::STATUS_WAITING_LIST),

                    Action::make('to_start_training')
                        ->label('بدء التدريب')
                        ->icon('heroicon-o-play')
                        ->form([
                            DatePicker::make('start_date')
                                ->label('تاريخ البدء')
                                ->required()
                                ->default(now()),
                            TextInput::make('duration')
                                ->label('المدة (يوم)')
                                ->numeric()
                                ->required()
                                ->default(30),
                        ])
                        ->action(function (Application $record, array $data) {
                            $startDate = \Carbon\Carbon::parse($data['start_date']);
                            $duration = (int)$data['duration'];
                            $endDate = $startDate->copy()->addDays($duration);
                            $record->update([
                                'status' => Application::STATUS_STARTED_TRAINING,
                                'start_date' => $startDate,
                                'duration' => $duration,
                                'end_date' => $endDate,
                            ]);
                        })
                        ->visible(fn(Application $record) => $record->status !== Application::STATUS_STARTED_TRAINING),

                    Action::make('to_end_training')
                        ->label('إنهاء التدريب')
                        ->icon('heroicon-o-stop')
                        ->requiresConfirmation()
                        ->action(fn(Application $record) => $record->update(['status' => Application::STATUS_ENDED_TRAINING]))
                        ->visible(fn(Application $record) => $record->status !== Application::STATUS_ENDED_TRAINING),

                    Action::make('to_reject')
                        ->label('رفض')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn(Application $record) => $record->update(['status' => Application::STATUS_REJECTED]))
                        ->visible(fn(Application $record) => $record->status !== Application::STATUS_REJECTED),

                    Action::make('to_dropped')
                        ->label('منسحب')
                        ->icon('heroicon-o-minus-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn(Application $record) => $record->update(['status' => Application::STATUS_DROPPED]))
                        ->visible(fn(Application $record) => $record->status !== Application::STATUS_DROPPED),
                ])
                    ->label('تغيير الحالة')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('primary')
                    ->visible(fn($livewire) => $livewire->activeTab === 'all'),

                // Group 2: Specific Actions (Visible in specific tabs)
                Action::make('initial_approve')
                    ->label('موافقة مبدئية')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn($record, $livewire) => $livewire->activeTab === 'new' && Auth::user()->isGeneralTrainingManager())
                    ->requiresConfirmation()
                    ->successNotificationTitle('تمت الموافقة المبدئية بنجاح')
                    ->action(fn($record) => $record->update(['status' => Application::STATUS_INITIAL_APPROVE])),

                Action::make('confirm_application')
                    ->label('تأكيد')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn($record, $livewire) => $livewire->activeTab === 'initial_approve' && (Auth::user()->isMinistry() || Auth::user()->isCollegeSupervisor()))
                    ->requiresConfirmation()
                    ->successNotificationTitle('تم تأكيد الطلب بنجاح')
                    ->action(fn($record) => $record->update(['status' => Application::STATUS_CONFIRMATION])),

                Action::make('start_training')
                    ->label('معالجة التأكيد')
                    ->color(fn(Application $record) => $record->section?->status ? 'success' : 'danger')
                    ->icon('heroicon-o-play')
                    ->visible(fn($record, $livewire) => $livewire->activeTab === 'confirmed' && Auth::user()->isGeneralTrainingManager())
                    ->modalHeading('معالجة التأكيد')
                    ->modalSubmitActionLabel(fn(Application $record) => $record->section?->status ? 'تأكيد' : 'نعم، متابعة')
                    ->form(fn(Application $record) => array_filter([
                        !$record->section?->status ? \Filament\Schemas\Components\Section::make('⚠️ تنبيه: القسم غير نشط')
                            ->description('القسم الحالي لهذا الطلب غير نشط. هل أنت متأكد من رغبتك في المتابعة؟')
                            ->icon('heroicon-o-exclamation-triangle')
                            ->iconColor('danger')
                            ->collapsed(false)
                            ->collapsible(false)
                            ->extraAttributes(['class' => 'bg-danger-50 dark:bg-danger-950 border-danger-300 dark:border-danger-700'])
                            ->schema([]) : null,
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
                            ->visible(fn(Get $get) => (int)$get('new_status') === Application::STATUS_STARTED_TRAINING),
                        TextInput::make('duration')
                            ->label('المدة (يوم)')
                            ->numeric()
                            ->required()
                            ->default(30)
                            ->reactive()
                            ->visible(fn(Get $get) => (int)$get('new_status') === Application::STATUS_STARTED_TRAINING),
                        \Filament\Forms\Components\Placeholder::make('calculated_end_date')
                            ->label('تاريخ الانتهاء المتوقع')
                            ->content(function (Get $get) {
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
                            ->visible(fn(Get $get) => (int)$get('new_status') === Application::STATUS_STARTED_TRAINING),
                    ]))
                    ->successNotificationTitle('تمت معالجة التأكيد بنجاح')
                    ->action(function (Application $record, array $data) {
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
                    ->visible(fn($record, $livewire) => $livewire->activeTab === 'confirmed' && $record->status == Application::STATUS_WAITING_LIST && Auth::user()->isGeneralTrainingManager())
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

                Action::make('end_training')
                    ->label('إنهاء التدريب')
                    ->color('warning')
                    ->icon('heroicon-o-stop')
                    ->visible(fn($record, $livewire) => $livewire->activeTab === 'training' && Auth::user()->isGeneralTrainingManager())
                    ->requiresConfirmation()
                    ->successNotificationTitle('تم إنهاء التدريب بنجاح')
                    ->action(fn($record) => $record->update(['status' => Application::STATUS_ENDED_TRAINING])),

                Action::make('reject')
                    ->label('رفض')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->modalHeading('رفض الطلب')
                    ->modalDescription('هل أنت متأكد من رفض هذا الطلب؟ سيتم نقله إلى قائمة المرفوضات.')
                    ->visible(fn(Application $record, $livewire) => in_array($livewire->activeTab, ['new', 'initial_approve', 'confirmed']) && (Auth::user()?->isAdmin() || Auth::user()?->isGeneralTrainingManager()))
                    ->action(function (Application $record) {
                        $record->update(['status' => Application::STATUS_REJECTED]);
                    })
                    ->successNotificationTitle('تم رفض الطلب'),

                Action::make('restore_rejection')
                    ->label('استعادة')
                    ->color('success')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->requiresConfirmation()
                    ->modalHeading('استعادة الطلب')
                    ->modalDescription('سيتم استعادة الطلب إلى قائمة الطلبات الجديدة (الحالة 1). هل أنت متأكد؟')
                    ->visible(fn(Application $record, $livewire) => $livewire->activeTab === 'rejected' && (Auth::user()?->isAdmin() || Auth::user()?->isGeneralTrainingManager()))
                    ->action(function (Application $record) {
                        $record->update(['status' => Application::STATUS_NEW]);
                    })
                    ->successNotificationTitle('تم استعادة الطلب لقائمة الطلبات الجديدة'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('reject_bulk')
                        ->label('رفض المختارة')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->visible(fn() => Auth::user()?->isAdmin() || Auth::user()?->isGeneralTrainingManager())
                        ->action(function ($records) {
                            $records->each(fn(Application $record) => $record->update(['status' => Application::STATUS_REJECTED]));
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('restore_rejection_bulk')
                        ->label('استعادة المرفوضة')
                        ->color('success')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->requiresConfirmation()
                        ->visible(fn() => Auth::user()?->isAdmin() || Auth::user()?->isGeneralTrainingManager())
                        ->action(function ($records) {
                            $records->each(fn(Application $record) => $record->update(['status' => Application::STATUS_NEW]));
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}

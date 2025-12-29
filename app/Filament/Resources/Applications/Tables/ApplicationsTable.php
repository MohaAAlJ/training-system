<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Models\Application;
use App\Models\Section;
use App\Models\Department;
use App\Models\Administrative;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\Constants;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Auth;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'asc')
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
                TrashedFilter::make()
                    ->label('الطلبات المرفوضة'),
            ])

            ->recordActions([
                ViewAction::make()
                    ->color('info')
                    ->outlined(),

                EditAction::make()
                    ->color('danger')
                    ->outlined(),

                Action::make('confirm_application')
                    ->label('تأكيد')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn($record) => (Auth::user()->isMinistry() || Auth::user()->isCollegeSupervisor()) && (int)$record->status === Application::STATUS_INITIAL_APPROVE)
                    ->requiresConfirmation()
                    ->successNotificationTitle('تم تأكيد الطلب بنجاح')
                    ->action(fn($record) => $record->update(['status' => Application::STATUS_CONFIRMATION])),

                Action::make('initial_approve')
                    ->label('موافقة مبدئية')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn($record) => Auth::user()->isGeneralTrainingManager() && $record->status == Application::STATUS_NEW)
                    ->requiresConfirmation()
                    ->successNotificationTitle('تمت الموافقة المبدئية بنجاح')
                    ->action(fn($record) => $record->update(['status' => Application::STATUS_INITIAL_APPROVE])),

                /*
                // New logic for inactive sections (disabled temporarily)
                Action::make('start_training')
                    ->label('معالجة التأكيد')
                    ->color('success')
                    ->icon('heroicon-o-play')
                    ->visible(fn($record) => Auth::user()->isGeneralTrainingManager() && $record->status == Application::STATUS_CONFIRMATION)
                    ->form(function (Application $record) {
                        $isSectionInactive = ! ($record->section?->status ?? false);

                        $schema = [];

                        if ($isSectionInactive) {
                            $schema[] = \Filament\Schemas\Components\Fieldset::make('تنبيه: القسم المسجل غير نشط')
                                ->columns(3)
                                ->schema([
                                    \Filament\Forms\Components\Select::make('administrative_id')
                                        ->label('الإدارة')
                                        ->options(Administrative::all()->pluck('name_with_governorate', 'id'))
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(fn (Set $set) => $set('department_id', null)),
                                    \Filament\Forms\Components\Select::make('department_id')
                                        ->label('الدائرة')
                                        ->options(fn (Get $get) => Department::whereHas('Section', fn ($q) => $q->where('administrative_id', $get('administrative_id')))->active()->pluck('title', 'id'))
                                        ->required()
                                        ->live()
                                        ->disabled(fn (Get $get) => ! $get('administrative_id'))
                                        ->afterStateUpdated(fn (Set $set) => $set('section_id', null)),
                                    \Filament\Forms\Components\Select::make('section_id')
                                        ->label('القسم')
                                        ->options(function (Get $get) {
                                            $adminId = $get('administrative_id');
                                            $deptId = $get('department_id');
                                            if (! $adminId || ! $deptId) return [];

                                            return Section::where('administrative_id', $adminId)
                                                ->where('department_id', $deptId)
                                                ->active()
                                                ->get()
                                                ->filter(fn ($sec) => ! ($sec->getCapacityStats()['is_full'] ?? false))
                                                ->pluck('name_location', 'id');
                                        })
                                        ->required()
                                        ->disabled(fn (Get $get) => ! $get('department_id')),
                                ]);
                        }

                        $schema[] = \Filament\Forms\Components\Select::make('new_status')
                            ->label('الحالة الجديدة')
                            ->options([
                                Application::STATUS_WAITING_LIST => 'قائمة الانتظار',
                                Application::STATUS_STARTED_TRAINING => 'بدء التدريب',
                            ])
                            ->required()
                            ->reactive()
                            ->default(Application::STATUS_STARTED_TRAINING);

                        $schema[] = DatePicker::make('start_date')
                            ->label('تاريخ البدء')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->format('Y/m/d')
                            ->displayFormat('Y/m/d')
                            ->reactive()
                            ->visible(fn(Get $get) => (int)$get('new_status') === Application::STATUS_STARTED_TRAINING);

                        $schema[] = TextInput::make('duration')
                            ->label('المدة (يوم)')
                            ->numeric()
                            ->required()
                            ->default(30)
                            ->reactive()
                            ->visible(fn(Get $get) => (int)$get('new_status') === Application::STATUS_STARTED_TRAINING);

                        $schema[] = \Filament\Forms\Components\Placeholder::make('calculated_end_date')
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
                            ->visible(fn(Get $get) => (int)$get('new_status') === Application::STATUS_STARTED_TRAINING);

                        return $schema;
                    })
                    ->successNotificationTitle('تمت معالجة التأكيد بنجاح')
                    ->action(function ($record, array $data) {
                        // Check if section was updated
                        if (isset($data['section_id'])) {
                             $record->update([
                                 'administrative_id' => $data['administrative_id'],
                                 'department_id' => $data['department_id'],
                                 'section_id' => $data['section_id'],
                             ]);
                             // Ensure relationship is fresh for subsequent logic if needed
                             $record->refresh();
                        }

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
                */


                // Single clean start_training action with warning banner
                Action::make('start_training')
                    ->label('معالجة التأكيد')
                    ->color(fn(Application $record) => $record->section?->status ? 'success' : 'danger')
                    ->icon('heroicon-o-play')
                    ->visible(fn($record) => Auth::user()->isGeneralTrainingManager() && $record->status == Application::STATUS_CONFIRMATION)
                    ->modalHeading('معالجة التأكيد')
                    ->modalSubmitActionLabel(fn(Application $record) => $record->section?->status ? 'تأكيد' : 'نعم، متابعة')
                    ->form(fn(Application $record) => array_filter([
                        // Warning alert - only shown when section is inactive
                        !$record->section?->status ? \Filament\Schemas\Components\Section::make('⚠️ تنبيه: القسم غير نشط')
                            ->description('القسم الحالي لهذا الطلب غير نشط. هل أنت متأكد من رغبتك في المتابعة؟')
                            ->icon('heroicon-o-exclamation-triangle')
                            ->iconColor('danger')
                            ->collapsed(false)
                            ->collapsible(false)
                            ->extraAttributes(['class' => 'bg-danger-50 dark:bg-danger-950 border-danger-300 dark:border-danger-700'])
                            ->schema([]) : null,

                        // Form fields
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
                    ->visible(fn($record) => !$record->trashed() && (Auth::user()?->isAdmin() || Auth::user()?->isGeneralTrainingManager()))
                    ->action(function ($record) {
                        $record->update(['status' => Application::STATUS_REJECTED]);
                        $record->delete();
                    }),
                RestoreAction::make()
                    ->label('استعادة')
                    ->visible(fn($record) => $record->trashed() && (Auth::user()?->isAdmin() || Auth::user()?->isGeneralTrainingManager())),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('رفض المختارة')
                        ->visible(fn() => Auth::user()?->isAdmin() || Auth::user()?->isGeneralTrainingManager())
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => Application::STATUS_REJECTED]);
                                $record->delete();
                            });
                        }),
                    RestoreBulkAction::make()
                        ->label('استعادة المرفوضة')
                        ->visible(fn() => Auth::user()?->isAdmin() || Auth::user()?->isGeneralTrainingManager()),
                ]),
            ]);
    }
}

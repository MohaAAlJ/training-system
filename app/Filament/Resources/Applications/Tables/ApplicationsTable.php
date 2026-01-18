<?php

declare(strict_types=1);

namespace App\Filament\Resources\Applications\Tables;

use App\Enums\ApplicationStatus;
use App\Enums\TrainingType;
use App\Models\Administrative;
use App\Models\Application;
use App\Models\Department;
use App\Models\Section;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class ApplicationsTable
{
    public static function configure(Table $table, ?string $statusTitle = null): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with([
                'trainee',
                'section:id,name_location,department_id,administrative_id,status',
                'department:id,title',
                'administrative:id,title',
            ]))
            ->columns([
                TextColumn::make('trainee.full_name')
                    ->label('الاسم الكامل')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('trainee.national_id')
                    ->label('رقم الهوية')
                    ->searchable(),

                TextColumn::make('department.title')
                    ->label('الدائرة')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('section.name_location')
                    ->label('القسم / الشعبة')
                    ->sortable(),

                TextColumn::make('training_type')
                    ->label('نوع التدريب')
                    ->badge(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('تاريخ الطلب')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(ApplicationStatus::class)
                    ->visible(fn() => $statusTitle === null && Auth::user()->isAdmin()),

                SelectFilter::make('training_type')
                    ->label('نوع التدريب')
                    ->options(TrainingType::class)
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),

                ActionGroup::make([
                    Action::make('initial_approve')
                        ->label('موافقة مبدئية')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn(Application $record) => $record->status === ApplicationStatus::NEW && (Auth::user()->isGeneralTrainingManager() || Auth::user()->isMinistry()))
                        ->requiresConfirmation()
                        ->action(function (Application $record) {
                            $record->update(['status' => ApplicationStatus::INITIAL_APPROVE, 'accepted_at' => now()]);
                            Notification::make()->title('تمت الموافقة المبدئية بنجاح')->success()->send();
                        }),

                    Action::make('confirm_application')
                        ->label('تأكيد الطلب')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn(Application $record) => $record->status === ApplicationStatus::INITIAL_APPROVE && (Auth::user()->isGeneralTrainingManager() || Auth::user()->isMinistry()))
                        ->requiresConfirmation()
                        ->action(function (Application $record) {
                            $record->update(['status' => ApplicationStatus::CONFIRMATION]);
                            Notification::make()->title('تم تأكيد الطلب بنجاح')->success()->send();
                        }),

                    Action::make('process_application')
                        ->label('معالجة الطلب')
                        ->icon('heroicon-o-cpu-chip')
                        ->color('primary')
                        ->visible(fn(Application $record) => in_array($record->status, [ApplicationStatus::CONFIRMATION, ApplicationStatus::WAITING_LIST]) && Auth::user()->isGeneralTrainingManager())
                        ->modalSubmitAction(function (Application $record) {
                            $isSectionInactive = ! ($record->section?->status ?? false);
                            $isSectionFull = $record->section?->getCapacityStats()['is_full'] ?? false;

                            if ($isSectionInactive || $isSectionFull) {
                                return false;
                            }

                            return null;
                        })
                        ->form(function (Application $record) {
                            $schema = [];
                            $sectionStats = $record->section?->getCapacityStats();
                            $isSectionInactive = ! ($record->section?->status ?? false);
                            $isSectionFull = $sectionStats['is_full'] ?? false;

                            if ($isSectionInactive || $isSectionFull) {
                                $warningTitle = $isSectionInactive ? 'تنبيه: القسم المسجل غير نشط' : 'تنبيه: القسم ممتلئ بالكامل';
                                $warningMessage = $isSectionInactive
                                    ? 'القسم المرتبط بالطلب حالياً غير نشط.'
                                    : "القسم المرتبط بالطلب ممتلئ (السعة: {$sectionStats['total']}).";

                                $schema[] = Placeholder::make('warning')
                                    ->label($warningTitle)
                                    ->content($warningMessage)
                                    ->columnSpanFull()
                                    ->extraAttributes(['class' => 'text-danger-600 font-bold']);

                                return $schema;
                            }

                            $defaultStatus = match ($record->status) {
                                ApplicationStatus::WAITING_LIST => ApplicationStatus::STARTED_TRAINING->value,
                                ApplicationStatus::CONFIRMATION => ApplicationStatus::WAITING_LIST->value,
                                default => ApplicationStatus::STARTED_TRAINING->value,
                            };

                            $schema[] = Select::make('new_status')
                                ->label('الحالة الجديدة')
                                ->options([
                                    ApplicationStatus::STARTED_TRAINING->value => ApplicationStatus::STARTED_TRAINING->getLabel(),
                                    ApplicationStatus::WAITING_LIST->value => ApplicationStatus::WAITING_LIST->getLabel(),
                                ])
                                ->default($defaultStatus)
                                ->required()
                                ->reactive();

                            $schema[] = DatePicker::make('start_date')
                                ->label('تاريخ بدء التدريب')
                                ->required()
                                ->visible(fn(Get $get) => $get('new_status') == ApplicationStatus::STARTED_TRAINING->value)
                                ->default(now()->toDateString())
                                ->displayFormat('Y/m/d')
                                ->native(false)
                                ->closeOnDateSelection()
                                ->reactive()
                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                    if ($state && $get('training_duration')) {
                                        $set('expected_finish_date', Carbon::parse($state)->addDays($get('training_duration'))->toDateString());
                                    }
                                });

                            $schema[] = TextInput::make('training_duration')
                                ->label('مدة التدريب (بالأيام)')
                                ->numeric()
                                ->required()
                                ->visible(fn(Get $get) => $get('new_status') == ApplicationStatus::STARTED_TRAINING->value)
                                ->default(30)
                                ->reactive()
                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                    if ($state && $get('start_date')) {
                                        $set('expected_finish_date', Carbon::parse($get('start_date'))->addDays($state)->toDateString());
                                    }
                                });

                            $schema[] = Hidden::make('expected_finish_date')
                                ->default(Carbon::parse(now())->addDays(30)->toDateString());

                            $schema[] = Placeholder::make('expected_finish_date_placeholder')
                                ->label('تاريخ الانتهاء المتوقع')
                                ->content(fn(Get $get) => $get('expected_finish_date') ?? 'يرجى تحديد تاريخ البدء ومدة التدريب')
                                ->visible(fn(Get $get) => $get('new_status') == ApplicationStatus::STARTED_TRAINING->value);

                            return $schema;
                        })
                        ->action(function (Application $record, array $data) {
                            if (! isset($data['new_status'])) {
                                return;
                            }

                            $updateData = ['status' => $data['new_status']];

                            if ($data['new_status'] == ApplicationStatus::STARTED_TRAINING->value) {
                                $updateData['start_date'] = $data['start_date'];
                                $updateData['end_date'] = Carbon::parse($data['start_date'])->addDays($data['training_duration']);
                            }

                            // if (isset($data['section_id'])) {
                            //     $updateData['section_id'] = $data['section_id'];
                            //     $updateData['department_id'] = $data['department_id'];
                            //     $updateData['administrative_id'] = $data['administrative_id'];
                            // }

                            $record->update($updateData);
                            Notification::make()->title('تمت معالجة الطلب بنجاح')->success()->send();
                        }),

                    Action::make('end_training')
                        ->label('إنهاء التدريب')
                        ->icon('heroicon-o-flag')
                        ->color('warning')
                        ->visible(fn(Application $record) => $record->status === ApplicationStatus::STARTED_TRAINING && Auth::user()->isGeneralTrainingManager())
                        ->form([
                            DatePicker::make('end_date')
                                ->label('تاريخ الانتهاء الفعلي')
                                ->default(now())
                                ->required(),
                        ])
                        ->action(function (Application $record, array $data) {
                            $record->update(['status' => ApplicationStatus::ENDED_TRAINING, 'end_date' => $data['end_date']]);
                            Notification::make()->title('تم إنهاء التدريب بنجاح')->success()->send();
                        }),

                    Action::make('reject')
                        ->label('رفض الطلب')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn(Application $record) => in_array($record->status, [ApplicationStatus::NEW, ApplicationStatus::INITIAL_APPROVE, ApplicationStatus::CONFIRMATION, ApplicationStatus::WAITING_LIST]) && Auth::user()->isGeneralTrainingManager())
                        ->requiresConfirmation()
                        ->action(function (Application $record) {
                            $record->update(['status' => ApplicationStatus::REJECTED]);
                            Notification::make()->title('تم رفض الطلب')->danger()->send();
                        }),

                    Action::make('restore_rejection')
                        ->label('استعادة من الرفض')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->visible(fn(Application $record) => $record->status === ApplicationStatus::REJECTED && Auth::user()->isGeneralTrainingManager())
                        ->requiresConfirmation()
                        ->action(function (Application $record) {
                            $record->update(['status' => ApplicationStatus::NEW]);
                            Notification::make()->title('تمت استعادة الطلب إلى جديد')->success()->send();
                        }),

                    Action::make('print_absorption_paper')
                        ->label('طباعة ورقة الاستيعاب')
                        ->icon('heroicon-o-printer')
                        ->url(fn(Application $record) => route('applications.download-absorption', ['application' => $record, 'mode' => 'view']))
                        ->openUrlInNewTab()
                        ->visible(fn(Application $record) => Auth::user()->isMinistry() &&
                            in_array($record->status, [
                                ApplicationStatus::INITIAL_APPROVE,
                                ApplicationStatus::STARTED_TRAINING,
                                ApplicationStatus::ENDED_TRAINING,
                            ]) &&
                            $record->training_type === TrainingType::PRACTICE),

                    Action::make('download_absorption_paper')
                        ->label('تحميل ورقة الاستيعاب')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn(Application $record) => route('applications.download-absorption', ['application' => $record, 'mode' => 'download']))
                        ->openUrlInNewTab()
                        ->visible(fn(Application $record) => Auth::user()->isMinistry() &&
                            in_array($record->status, [
                                ApplicationStatus::INITIAL_APPROVE,
                                ApplicationStatus::STARTED_TRAINING,
                                ApplicationStatus::ENDED_TRAINING,
                            ]) &&
                            $record->training_type === TrainingType::PRACTICE),
                ])->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('change_status')
                        ->label('تغيير الحالة')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Select::make('status')
                                ->label('الحالة الجديدة')
                                ->options(ApplicationStatus::class)
                                ->required()
                                ->reactive(),
                            DatePicker::make('start_date')
                                ->label('تاريخ بدء التدريب')
                                ->visible(fn(Get $get) => in_array($get('status'), [ApplicationStatus::STARTED_TRAINING, ApplicationStatus::STARTED_TRAINING->value]))
                                ->required(fn(Get $get) => in_array($get('status'), [ApplicationStatus::STARTED_TRAINING, ApplicationStatus::STARTED_TRAINING->value]))
                                ->default(now()->toDateString())
                                ->displayFormat('Y/m/d')
                                ->native(false)
                                ->closeOnDateSelection()
                                ->reactive()
                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                    if ($state && $get('duration')) {
                                        $set('expected_finish_date', Carbon::parse($state)->addDays($get('duration'))->toDateString());
                                    }
                                }),
                            TextInput::make('duration')
                                ->label('مدة التدريب (بالأيام)')
                                ->numeric()
                                ->visible(fn(Get $get) => in_array($get('status'), [ApplicationStatus::STARTED_TRAINING, ApplicationStatus::STARTED_TRAINING->value]))
                                ->required(fn(Get $get) => in_array($get('status'), [ApplicationStatus::STARTED_TRAINING, ApplicationStatus::STARTED_TRAINING->value]))
                                ->default(30)
                                ->reactive()
                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                    if ($state && $get('start_date')) {
                                        $set('expected_finish_date', Carbon::parse($get('start_date'))->addDays($state)->toDateString());
                                    }
                                }),
                            Hidden::make('expected_finish_date')
                                ->default(Carbon::parse(now())->addDays(30)->toDateString()),
                            Placeholder::make('expected_finish_date_placeholder')
                                ->label('تاريخ الانتهاء المتوقع')
                                ->content(fn(Get $get) => $get('expected_finish_date') ?? 'يرجى تحديد تاريخ البدء ومدة التدريب')
                                ->visible(fn(Get $get) => in_array($get('status'), [ApplicationStatus::STARTED_TRAINING, ApplicationStatus::STARTED_TRAINING->value])),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $status = $data['status'];
                            if (! $status instanceof ApplicationStatus) {
                                $status = ApplicationStatus::from($status);
                            }

                            $records->each(function (Application $record) use ($status, $data) {
                                $updateData = ['status' => $status];
                                if ($status === ApplicationStatus::STARTED_TRAINING) {
                                    $updateData['start_date'] = $data['start_date'];
                                    $updateData['end_date'] = Carbon::parse($data['start_date'])->addDays($data['duration']);
                                }
                                $record->update($updateData);
                            });

                            Notification::make()->title('تم تحديث الحالات بنجاح')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}

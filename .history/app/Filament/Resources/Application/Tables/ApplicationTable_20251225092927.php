<?php

namespace App\Filament\Resources\Application\Tables;

use App\Models\Application;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\Constants;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Auth;

class ApplicationTable
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
                    )),
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

                Action::make('start_training')
                    ->label('معالجة التأكيد')
                    ->color('success')
                    ->icon('heroicon-o-play')
                    ->visible(fn($record) => Auth::user()->isGeneralTrainingManager() && $record->status == Application::STATUS_CONFIRMATION)
                    ->form([
                        \Filament\Forms\Components\Select::make('new_status')
                            ->label('الحالة الجديدة')
                            ->options([
                                Application::STATUS_WAITING_LIST => 'قائمة الانتظار',
                                Application::STATUS_STRATED_TRAINING => 'بدء التدريب',
                            ])
                            ->required()
                            ->reactive()
                            ->default(Application::STATUS_STRATED_TRAINING),
                        DatePicker::make('start_date')
                            ->label('تاريخ البدء')
                            ->required()
                            ->default(now())
                            ->visible(fn($get) => (int)$get('new_status') === Application::STATUS_STRATED_TRAINING),
                        TextInput::make('duration')
                            ->label('المدة (يوم)')
                            ->numeric()
                            ->required()
                            ->default(45)
                            ->visible(fn($get) => (int)$get('new_status') === Application::STATUS_STRATED_TRAINING),
                    ])
                    ->successNotificationTitle('تمت معالجة التأكيد بنجاح')
                    ->action(function ($record, array $data) {
                        $newStatus = (int)$data['new_status'];

                        if ($newStatus === Application::STATUS_STRATED_TRAINING) {
                            $startDate = \Carbon\Carbon::parse($data['start_date']);
                            $duration = (int)$data['duration'];
                            $endDate = $startDate->copy()->addDays($duration);

                            $record->update([
                                'status' => Application::STATUS_STRATED_TRAINING,
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

                DeleteAction::make()
                    ->label('رفض')
                    ->modalHeading('رفض الطلب')
                    ->modalDescription('هل أنت متأكد من رفض هذا الطلب؟ سيتم نقله إلى قائمة المرفوضات.')
                    ->action(function ($record) {
                        $record->update(['status' => Application::STATUS_REJECTED]);
                        $record->delete();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('رفض المختارة')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => Application::STATUS_REJECTED]);
                                $record->delete();
                            });
                        }),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make()
                        ->label('استعادة المرفوضة'),
                ]),
            ]);
    }
}







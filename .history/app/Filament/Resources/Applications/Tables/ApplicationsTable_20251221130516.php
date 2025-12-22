<?php

namespace App\Filament\Resources\Applications\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\Constans;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\DatePicker;

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
                    ->formatStateUsing(fn($state) => is_array($state) ? ($state['ar'] ?? $state['en'] ?? reset($state)) : $state)
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('trainee.major.name')
                    ->label('التخصص')
                    ->formatStateUsing(fn($state) => is_array($state) ? ($state['ar'] ?? $state['en'] ?? reset($state)) : $state)
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('administrative.title')
                    ->label('الإدارة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.title')
                    ->label('الدائرة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('section.name_location')
                    ->label('القسم')
                    ->searchable()
                    ->sortable(),
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
                    ->badge()
                    ->color(fn($state) => Constans::STATUS_COLORS[$state] ?? 'gray')
                    ->formatStateUsing(fn($state) => Constans::STATUS_LABELS[$state] ?? $state),

                // Training Manager: Approve (1 -> 2)
                ToggleColumn::make('approve_initial')
                    ->label('موافقة مبدئية')
                    ->state(fn($record) => $record->status >= Constans::STATUS_INITIAL_APPROVE)
                    ->onColor('success')
                    ->offColor('danger')
                    ->disabled(fn($record) => $record->status > Constans::STATUS_INITIAL_APPROVE) // Disable if already progressed further
                    ->visible(fn() => Auth::user()->isGeneralTrainingManager())
                    ->updateStateUsing(function ($record, $state) {
                        if ($state) {
                            $record->update(['status' => Constans::STATUS_INITIAL_APPROVE]);
                        } else {
                            $record->update(['status' => Constans::STATUS_NEW]);
                        }
                    }),

                // College Supervisor (2 -> 3) OR Ministry (1 -> 2)
                ToggleColumn::make('confirm_acceptance')
                    ->label('تأكيد القبول')
                    ->state(function ($record) {
                        // For Ministry, "ON" means Status is at least 2 (Initial Approve)
                        if (Auth::user()->isMinistry()) {
                            return $record->status >= Constans::STATUS_INITIAL_APPROVE;
                        }
                        // For College, "ON" means Status is at least 3 (Confirmation)
                        return $record->status >= Constans::STATUS_CONFIRMATION;
                    })
                    ->onColor('success')
                    ->offColor('danger')
                    ->disabled(function ($record) {
                        if (Auth::user()->isMinistry()) {
                            // Enabled for Status 1 (to turn ON) or Status 2 (to turn OFF). Disabled if progressed further (> 2).
                            return $record->status > Constans::STATUS_INITIAL_APPROVE;
                        }
                        // Existing logic for College Supervisor
                        return $record->status < Constans::STATUS_INITIAL_APPROVE || $record->status > Constans::STATUS_CONFIRMATION;
                    })
                    ->visible(fn() => Auth::user()->isCollegeSupervisor() || Auth::user()->isMinistry())
                    ->updateStateUsing(function ($record, $state) {
                        if (Auth::user()->isMinistry()) {
                            // Ministry Flow: 1 <-> 2
                            $record->update(['status' => $state ? Constans::STATUS_INITIAL_APPROVE : Constans::STATUS_NEW]);
                        } else {
                            // College Flow: 2 <-> 3
                            if ($state) {
                                $record->update(['status' => Constans::STATUS_CONFIRMATION]);
                            } else {
                                $record->update(['status' => Constans::STATUS_INITIAL_APPROVE]);
                            }
                        }
                    }),

                // HOS: Start Training (4 -> 5)
                ToggleColumn::make('start_training')
                    ->label('بدء التدريب')
                    ->state(fn($record) => $record->status >= Constans::STATUS_START_TRAINING)
                    ->onColor('success')
                    ->offColor('danger')
                    ->disabled(fn($record) => $record->status < Constans::STATUS_WAITING || $record->status > Constans::STATUS_START_TRAINING)
                    ->visible(fn() => Auth::user()->isSectionHead())
                    ->updateStateUsing(function ($record, $state) {
                        if ($state) {
                            $record->update(['status' => Constans::STATUS_START_TRAINING]);
                        } else {
                            $record->update(['status' => Constans::STATUS_WAITING]);
                        }
                    }),

                TextColumn::make('duration')
                    ->label('مدة التدريب (أيام)')
                    ->getStateUsing(fn($record) => $record->start_date && $record->end_date ? $record->end_date->diffInDays($record->start_date) : '-')
                    ->sortable(query: fn(Builder $query, string $direction) => $query->orderBy('end_date', $direction))
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->options(Constans::STATUS_LABELS),
                SelectFilter::make('department_id')
                    ->label('الدائرة')
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
                TrashedFilter::make(),
            ])

            ->recordActions([
                ViewAction::make()
                    ->color('info')
                    ->outlined(),

                EditAction::make()
                    ->color('danger')
                    ->outlined()
                    ->visible(fn() => !Auth::user()->isGeneralTrainingManager()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}

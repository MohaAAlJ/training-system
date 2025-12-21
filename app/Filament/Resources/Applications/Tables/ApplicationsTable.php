<?php

namespace App\Filament\Resources\Applications\Tables;

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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\Constans;
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
                TextColumn::make('department.title')
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
                    ->color(fn ($state): string => match ((int)$state) {
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
                    ->formatStateUsing(fn ($state): string => (function($state) {
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
                    ->options(fn () => array_combine(
                        Constans::STATUSES,
                        array_map(fn($s) => \Illuminate\Support\Facades\Lang::get("translation.status.$s", [], 'ar'), Constans::STATUSES)
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
                TrashedFilter::make(),
            ])

            ->recordActions([
                ViewAction::make()
                    ->color('info')
                    ->outlined(),

                EditAction::make()
                    ->color('danger')
                    ->outlined(),
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

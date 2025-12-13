<?php

namespace App\Filament\Resources\Applications\Tables;

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
                    ->formatStateUsing(fn ($state) => is_array($state) ? ($state['ar'] ?? $state['en'] ?? reset($state)) : $state)
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('trainee.major.name')
                    ->label('التخصص')
                    ->formatStateUsing(fn ($state) => is_array($state) ? ($state['ar'] ?? $state['en'] ?? reset($state)) : $state)
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('department.name_location')
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
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'info',
                        'waiting' => 'warning',
                        'approved' => 'primary',
                        'active' => 'success',
                        'completed' => 'gray',
                        'rejected' => 'danger',
                        'paused' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'طلب جديد',
                        'waiting' => 'استيعاب',
                        'approved' => 'قبول جامعة',
                        'active' => 'بدء العمل',
                        'completed' => 'انتهى',
                        'rejected' => 'مرفوض',
                        'paused' => 'منقطع',
                        default => $state,
                    }),
                TextColumn::make('duration')
                    ->label('مدة التدريب (أيام)')
                    ->getStateUsing(fn ($record) => $record->start_date && $record->end_date ? $record->end_date->diffInDays($record->start_date) : '-')
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('end_date', $direction))
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
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'طلب جديد',
                        'waiting' => 'استيعاب',
                        'approved' => 'قبول جامعة',
                        'active' => 'بدء العمل',
                        'completed' => 'انتهى',
                        'rejected' => 'مرفوض',
                        'paused' => 'منقطع',
                    ]),
                SelectFilter::make('department_id')
                    ->label('القسم')
                    ->relationship('department', 'name_location')
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
                        \Filament\Forms\Components\DatePicker::make('start_date_from')
                            ->label('من'),
                        \Filament\Forms\Components\DatePicker::make('start_date_to')
                            ->label('إلى'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['start_date_from'], fn (Builder $q) => $q->whereDate('start_date', '>=', $data['start_date_from']))
                            ->when($data['start_date_to'], fn (Builder $q) => $q->whereDate('start_date', '<=', $data['start_date_to']));
                    }),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
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

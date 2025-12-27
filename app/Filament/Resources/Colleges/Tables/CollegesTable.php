<?php

namespace App\Filament\Resources\Colleges\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CollegesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('رقم')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('اسم الكلية')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('institution.name')
                    ->label('الجامعة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('مشرف الكلية')
                    ->searchable()
                    ->placeholder('غير محدد'),
                \Filament\Tables\Columns\ToggleColumn::make('is_active')
                    ->label('الحالة')
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->sortable(),
                \Filament\Tables\Columns\ToggleColumn::make('Can_add_Application')
                    ->label('إضافة طلبات')
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->sortable(),
                TextColumn::make('trainees_count')
                    ->label('عدد المتدربين')
                    ->counts('trainees')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
                \Filament\Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط')
                    ->placeholder('الكل'),
                \Filament\Tables\Filters\SelectFilter::make('institution_id')
                    ->label('الجامعة')
                    ->relationship('institution', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                \Filament\Actions\DeleteAction::make()->visible(fn($record) => !$record->trashed() && \Illuminate\Support\Facades\Auth::user()?->isAdmin()),
                \Filament\Actions\RestoreAction::make()->visible(fn($record) => $record->trashed() && \Illuminate\Support\Facades\Auth::user()?->isAdmin()),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn() => \Illuminate\Support\Facades\Auth::user()?->isAdmin() ?? false),
                    RestoreBulkAction::make()->visible(fn() => \Illuminate\Support\Facades\Auth::user()?->isAdmin() ?? false),
                ]),
            ]);
    }
}

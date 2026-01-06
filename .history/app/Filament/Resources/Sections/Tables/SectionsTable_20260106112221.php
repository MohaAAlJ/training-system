<?php

namespace App\Filament\Resources\Sections\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class SectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_location')
                    ->label('اسم القسم والموقع')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('administrative.title')
                    ->label('الإدارة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.title')
                    ->label('الدائرة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('المسؤول')
                    ->searchable(),
                TextColumn::make('capacity')
                    ->label('السعة')
                    ->sortable(),
                TextColumn::make('registered_count')
                    ->label('المسجلين')
                    ->state(function ($record) {
                        return \App\Models\Application::where('section_id', $record->id)
                            ->whereIn('status', [
                                \App\Models\Application::STATUS_STARTED_TRAINING,
                                \App\Models\Application::STATUS_ENDED_TRAINING,
                            ])
                            ->count();
                    })
                    ->badge()
                    ->color('primary'),
                ToggleColumn::make('status')
                    ->label('الحالة')
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->visible(fn() => Auth::user()?->isMedicalManager() ?? false),


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
                        '1' => 'نشط',
                        '0' => 'غير نشط',
                    ]),
                SelectFilter::make('administrative_id')
                    ->label('الدائرة')
                    ->relationship('administrative', 'title')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('user_id')
                    ->label('المسؤول')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()->visible(fn($record) => !$record->trashed() && Auth::user()?->isAdmin()),
                RestoreAction::make()->visible(fn($record) => $record->trashed() && Auth::user()?->isAdmin()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn() => Auth::user()?->isAdmin() ?? false),
                    RestoreBulkAction::make()->visible(fn() => Auth::user()?->isAdmin() ?? false),
                ]),
            ]);
    }
}

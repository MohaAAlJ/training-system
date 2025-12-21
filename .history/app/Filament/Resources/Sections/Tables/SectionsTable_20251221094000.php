<?php

namespace App\Filament\Resources\Sections\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_location')
                    ->label('اسم القسم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('administrative.title')
                    ->label('الادارة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.title')
                    ->label('الدائرة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('hosUser.name')
                    ->label('رئيس القسم')
                    ->searchable(),
                TextColumn::make('total_capacity')
                    ->label('السعة')
                    ->sortable(),
                ToggleColumn::make('status')
                    ->label('الحالة'),
            ])
            ->filters([
                SelectFilter::make('administrative_id')
                    ->label('المديرية')
                    ->relationship('administrative', 'title')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('department_id')
                    ->label('القسم')
                    ->relationship('department', 'title')
                    ->searchable()
                    ->preload(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()->visible(fn() => Auth::user()?->isAdmin() ?? false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn() => Auth::user()?->isAdmin() ?? false),
                    ForceDeleteBulkAction::make()->visible(fn() => Auth::user()?->isAdmin() ?? false),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}

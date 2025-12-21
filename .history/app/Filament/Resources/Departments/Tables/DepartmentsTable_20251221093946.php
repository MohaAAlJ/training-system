<?php

namespace App\Filament\Resources\Departments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DepartmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('اسم الدائرة')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_medical')
                    ->label('طبي')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('hodUser.name')
                    ->label('رئيس الدائرة')
                    ->searchable(),
                TextColumn::make('sections_count')
                    ->label('عدد الأقسام')
                    ->counts('sections')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_medical')
                    ->label('النوع')
                    ->options([
                        true => 'طبي',
                        false => 'غير طبي',
                    ]),
                SelectFilter::make('hod')
                    ->label('رئيس الدائرة')
                    ->relationship('hodUser', 'name')
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

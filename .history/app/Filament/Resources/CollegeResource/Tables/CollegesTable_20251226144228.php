<?php

namespace App\Filament\Resources\CollegeResource\Tables;

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
                TextColumn::make('Trainee_count')
                    ->label('عدد المتدربين')
                    ->counts('Trainee')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
                \Filament\Tables\Filters\SelectFilter::make('institution_id')
                    ->label('الجامعة')
                    ->relationship('institution', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
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

<?php

namespace App\Filament\Resources\AdministrativeResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class AdministrativesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('اسم الإدارة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('رئيس الإدارة')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_medical')
                    ->label('إدارة طبية')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('medicalHead.name')
                    ->label('رئيس الإدارة الطبية')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('رئيس الإدارة')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('medical_head_user_id')
                    ->label('رئيس الإدارة الطبية')
                    ->relationship('medicalHead', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('is_medical')
                    ->label('نوع الإدارة')
                    ->options([
                        true => 'إدارة طبية',
                        false => 'إدارة عامة',
                    ]),
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

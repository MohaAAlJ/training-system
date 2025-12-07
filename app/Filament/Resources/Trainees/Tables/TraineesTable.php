<?php

namespace App\Filament\Resources\Trainees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TraineesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('national_id')
                    ->label('رقم الهوية')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('full_name')
                    ->label('الاسم الكامل')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('phone_number')
                    ->label('رقم الهاتف'),
                TextColumn::make('address')
                    ->label('العنوان')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('dob')
                    ->label('تاريخ الميلاد')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('institutionMajor.name')
                    ->label('التخصص'),
                TextColumn::make('institution.name')
                    ->label('المؤسسة'),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('تاريخ الحذف')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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

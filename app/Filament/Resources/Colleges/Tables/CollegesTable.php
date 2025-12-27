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
                    ->sortable()
                    ->requiresConfirmation()
                    ->modalHeading('تغيير حالة الكلية')
                    ->modalDescription('هل أنت متأكد من أنك تريد تغيير حالة هذه الكلية؟')
                    ->modalSubmitActionLabel('نعم، قم بالتغيير')
                    ->modalCancelActionLabel('إلغاء'),
                \Filament\Tables\Columns\ToggleColumn::make('Can_add_Application')
                    ->label('إضافة طلبات')
                    ->sortable()
                    ->requiresConfirmation()
                    ->modalHeading('تغيير صلاحية إضافة الطلبات')
                    ->modalDescription('هل أنت متأكد من أنك تريد تغيير صلاحية إضافة الطلبات لهذه الكلية؟')
                    ->modalSubmitActionLabel('نعم، قم بالتغيير')
                    ->modalCancelActionLabel('إلغاء'),
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

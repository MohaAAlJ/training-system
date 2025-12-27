<?php

namespace App\Filament\Resources\Institutions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class InstitutionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\ToggleColumn::make('is_active')
                    ->label('الحالة')
                    ->sortable()
                    ->requiresConfirmation()
                    ->modalHeading('تغيير حالة المؤسسة/الجامعة')
                    ->modalDescription('هل أنت متأكد من أنك تريد تغيير حالة هذه المؤسسة؟')
                    ->modalSubmitActionLabel('نعم، قم بالتغيير')
                    ->modalCancelActionLabel('إلغاء'),
                \Filament\Tables\Columns\ToggleColumn::make('Can_add_Application')
                    ->label('إضافة طلبات')
                    ->sortable()
                    ->requiresConfirmation()
                    ->modalHeading('تغيير صلاحية إضافة الطلبات')
                    ->modalDescription('هل أنت متأكد من أنك تريد تغيير صلاحية إضافة الطلبات لهذه المؤسسة؟')
                    ->modalSubmitActionLabel('نعم، قم بالتغيير')
                    ->modalCancelActionLabel('إلغاء'),
                \Filament\Tables\Columns\TextColumn::make('trainees_count')
                    ->counts('trainees')
                    ->label('عدد المتدربين')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                \Filament\Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط')
                    ->placeholder('الكل'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()->visible(fn() => Auth::user()?->isAdmin() ?? false),
                RestoreAction::make()->visible(fn() => Auth::user()?->isAdmin() ?? false),
                ForceDeleteAction::make()->visible(fn() => Auth::user()?->isAdmin() ?? false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn() => Auth::user()?->isAdmin() ?? false),
                    ForceDeleteBulkAction::make()->visible(fn() => Auth::user()?->isAdmin() ?? false),
                    RestoreBulkAction::make()->visible(fn() => Auth::user()?->isAdmin() ?? false),
                ]),
            ]);
    }
}

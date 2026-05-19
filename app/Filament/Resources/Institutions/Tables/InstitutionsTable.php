<?php

namespace App\Filament\Resources\Institutions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
                \Filament\Tables\Columns\ToggleColumn::make('active')
                    ->label('الحالة')
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->sortable(),
                \Filament\Tables\Columns\ToggleColumn::make('add_application')
                    ->label('إضافة طلبات')
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->sortable(),
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
                TrashedFilter::make()->visible(fn() => Auth::user()?->isAdmin() ?? false),
                \Filament\Tables\Filters\TernaryFilter::make('active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط')
                    ->placeholder('الكل'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn($record) => !$record->trashed() && Auth::user()?->isAdmin())
                    ->before(function ($record, \Filament\Actions\DeleteAction $action) {
                        if ($record->colleges()->exists()) {
                            \Filament\Notifications\Notification::make()
                                ->title('لا يمكن الأرشفة')
                                ->body('لا يمكن أرشفة هذا السجل لوجود كليات مرتبطة به.')
                                ->danger()
                                ->send();
                            $action->halt();
                        }
                    })
                    ->action(function ($record) {
                        try {
                            $record->delete();
                        } catch (\Illuminate\Database\QueryException $exception) {
                            $errorCode = $exception->errorInfo[1] ?? 0;
                            if ($errorCode == 1451) {
                                \Filament\Notifications\Notification::make()
                                    ->title('لا يمكن الحذف')
                                    ->body('لا يمكن حذف هذا السجل نظرًا لوجود بيانات مرتبطة به.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            throw $exception;
                        }
                    }),
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

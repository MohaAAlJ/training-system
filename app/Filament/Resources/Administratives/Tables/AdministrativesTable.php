<?php

namespace App\Filament\Resources\Administratives\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
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
                TextColumn::make('name')
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
                TextColumn::make('active')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn($state): string => \App\Enums\GeneralConst::getStatusColor((int) $state))
                    ->formatStateUsing(fn($state): string => \App\Enums\GeneralConst::getStatusLabel((int) $state))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // SelectFilter::make('user_id')
                //     ->label('رئيس الإدارة')
                //     ->relationship('user', 'name')
                //     ->searchable()
                //     ->preload(),
                // SelectFilter::make('medical_head_user_id')
                //     ->label('رئيس الإدارة الطبية')
                //     ->relationship('medicalHead', 'name')
                //     ->searchable()
                //     ->preload(),
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
                DeleteAction::make()
                    ->visible(fn($record) => !$record->trashed() && Auth::user()?->isAdmin())
                    ->before(function ($record, \Filament\Actions\DeleteAction $action) {
                        if ($record->sections()->exists()) {
                            \Filament\Notifications\Notification::make()
                                ->title('لا يمكن الأرشفة')
                                ->body('لا يمكن أرشفة هذا السجل لوجود أقسام مرتبطة به.')
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

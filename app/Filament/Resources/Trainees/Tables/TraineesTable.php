<?php

namespace App\Filament\Resources\Trainees\Tables;

use App\Filament\Resources\Applications\Tables\ApplicationsTable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
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
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('full_name')
                    ->label('الاسم الكامل')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('gender')
                    ->label('الجنس')
                    ->badge(),
                TextColumn::make('phone_number')
                    ->label('رقم الهاتف')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('dob')
                    ->label('تاريخ الميلاد')
                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('Y-m-d') : null)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('governorate.name')
                    ->label('المحافظة')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('street')
                    ->label('الشارع')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('applications_count')
                    ->label('عدد الطلبات')
                    ->badge()
                    ->color('primary')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                ApplicationsTable::getImportExcelAction(),
            ])
            ->filters([

                SelectFilter::make('governorate_id')
                    ->label('المحافظة')
                    ->relationship('governorate', 'name')
                    ->searchable()
                    ->preload()
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['value']) {
                            return null;
                        }
                        return 'المحافظة: ' . \App\Models\Governorate::find($data['value'])?->name;
                    }),

                TrashedFilter::make()->visible(fn() => Auth::user()?->isAdmin() || Auth::user()?->isTrainingManagerLike()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()->visible(fn() => !Auth::user()->isMonitor()),
                DeleteAction::make()
                    ->visible(fn($record) => !Auth::user()->isMonitor() && !$record->trashed() && (Auth::user()?->isAdmin() || Auth::user()?->isTrainingManagerLike()))
                    ->before(function ($record, DeleteAction $action) {
                        if ($record->applications()->exists()) {
                            \Filament\Notifications\Notification::make()
                                ->title('لا يمكن الأرشفة')
                                ->body('لا يمكن أرشفة هذا السجل لوجود طلبات تدريب مرتبطة به.')
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
                RestoreAction::make()->visible(fn($record) => !Auth::user()->isMonitor() && $record->trashed() && (Auth::user()?->isAdmin() || Auth::user()?->isTrainingManagerLike())),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn() => !Auth::user()->isMonitor() && (Auth::user()?->isAdmin() || Auth::user()?->isTrainingManagerLike())),
                    RestoreBulkAction::make()->visible(fn() => !Auth::user()->isMonitor() && (Auth::user()?->isAdmin() || Auth::user()?->isTrainingManagerLike())),
                ]),
            ]);
    }
}

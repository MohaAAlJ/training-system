<?php

namespace App\Filament\Resources\Departments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\ToggleButtons;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Columns\IconColumn;

class DepartmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم الدائرة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('رئيس الدائرة')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_medical')
                    ->label('إدارة طبية')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sections_count')
                    ->label('عدد الأقسام')
                    ->counts('sections')
                    ->sortable(),
                ToggleColumn::make('active')
                    ->label('الحالة')
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->visible(function () {
                        $user = Auth::user();
                        if (!$user) return false;
                        if ($user->isAdmin()) return true;
                        return !$user->isMonitor();
                    })
                    ->sortable(),
                ToggleColumn::make('visible')
                    ->label('الظهور للمستخدمين')
                    ->visible(fn() => Auth::user()?->isAdmin() ?? false)
                    ->onIcon('heroicon-m-eye')
                    ->offIcon('heroicon-m-eye-slash')
                    ->onColor('success')
                    ->offColor('danger')
                    ->sortable()
                    ->toggleable(),


                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('administrative')
                    ->label('الإدارة')
                    ->options(fn() => \App\Models\Administrative::active()->pluck('name', 'id'))
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            return $query->whereHas(
                                'sections',
                                fn($q) => $q
                                    ->where('administrative_id', $data['value'])
                                    ->whereHas('administrative', fn($aq) => $aq->active())
                            );
                        }
                        return $query;
                    })
                    ->searchable()
                    ->preload()
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['value']) {
                            return null;
                        }
                        return 'الإدارة: ' . \App\Models\Administrative::find($data['value'])?->name;
                    }),

                SelectFilter::make('is_medical')
                    ->label('نوع الإدارة')
                    ->options([
                        true => 'إدارة طبية',
                        false => 'إدارة عامة',
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if ($data['value'] === null) return null;
                        return $data['value'] ? 'نوع الإدارة: طبية' : 'نوع الإدارة: عامة';
                    }),
                TrashedFilter::make()->visible(fn() => Auth::user()?->isAdmin() ?? false),
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

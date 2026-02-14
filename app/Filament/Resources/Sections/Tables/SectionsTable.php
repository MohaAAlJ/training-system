<?php

namespace App\Filament\Resources\Sections\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم القسم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('administrative.name')
                    ->label('الإدارة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('department.name')
                    ->label('الدائرة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label('المسؤول')
                    ->searchable(),
                TextColumn::make('capacity')
                    ->label('السعة')
                    ->sortable(),
                TextColumn::make('registered_count')
                    ->label('المسجلين')
                    ->state(function ($record) {
                        return \App\Models\Application::where('section_id', $record->id)
                            ->whereIn('status', [
                                \App\Models\Application::STATUS_STARTED_TRAINING,
                                \App\Models\Application::STATUS_ENDED_TRAINING,
                            ])
                            ->count();
                    })
                    ->badge()
                    ->color('primary')
                    ->toggleable()
                    ->sortable(),
                ToggleColumn::make('active')
                    ->label('الحالة')
                    ->sortable()
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->visible(function () {
                        $user = Auth::user();
                        if (!$user) return false;

                        if ($user->isAdmin()) return true;

                        if ($user->isGeneralTrainingManager()) return false;

                        $settings = app(\App\Settings\TrainingSettings::class);

                        if ($user->isHOA()) {
                            return $settings->hoa_can_enable_section;
                        }

                        if ($user->isDepartment()) {
                            return $settings->dept_head_can_enable_section;
                        }

                        if ($user->isMedicalManager()) return False;

                        return false;
                    }),


                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('active')
                    ->label('الحالة')
                    ->options([
                        '1' => 'نشط',
                        '0' => 'غير نشط',
                    ]),
                \Filament\Tables\Filters\Filter::make('sections_filter')
                    ->form([
                        \Filament\Forms\Components\Select::make('administrative_id')
                            ->label('الإدارة')
                            ->relationship('administrative', 'name', fn ($query) => $query->active())
                            ->searchable()
                            ->preload()
                            ->reactive(),
                        \Filament\Forms\Components\Select::make('department_id')
                            ->label('الدائرة')
                            ->options(function ($get) {
                                $adminId = $get('administrative_id');
                                if (!$adminId) {
                                    return \App\Models\Department::active()->pluck('name', 'id');
                                }
                                return \App\Models\Section::where('administrative_id', $adminId)
                                    ->active()
                                    ->whereHas('administrative', fn ($q) => $q->active())
                                    ->whereHas('department', fn ($q) => $q->active())
                                    ->with('department')
                                    ->get()
                                    ->pluck('department.name', 'department.id')
                                    ->filter();
                            })
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['administrative_id'],
                                fn (Builder $query, $value): Builder => $query->where('administrative_id', $value),
                            )
                            ->when(
                                $data['department_id'],
                                fn (Builder $query, $value): Builder => $query->where('department_id', $value),
                            );
                    }),
                // SelectFilter::make('user_id')
                //     ->label('المسؤول')
                //     ->relationship('user', 'name')
                //     ->searchable()
                //     ->preload(),
                // TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn($record) => Auth::user()->can('editDetails', $record)),
                DeleteAction::make()
                    ->visible(fn($record) => !$record->trashed() && Auth::user()?->isAdmin())
                    ->before(function ($record, \Filament\Actions\DeleteAction $action) {
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

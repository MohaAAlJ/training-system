<?php

namespace App\Filament\Resources\Trainees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
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
                    ->searchable(),
                TextColumn::make('full_name')
                    ->label('الاسم الكامل')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('phone_number')
                    ->label('رقم الهاتف')
                    ->searchable(),
                TextColumn::make('dob')
                    ->label('تاريخ الميلاد')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('street')
                    ->label('المنطقة / الشارع')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('institution.name')
                    ->label('المؤسسة التعليمية')
                    ->sortable()
                    ->visible(fn() => Auth::check() && (
                        Auth::user()->isAdmin() ||
                        Auth::user()->isDepartment() ||
                        Auth::user()->isHOA() ||
                        Auth::user()->isGeneralTrainingManager()
                    )),
                TextColumn::make('major.name')
                    ->label('التخصص')
                    ->sortable()
                    ->visible(fn() => Auth::check() && (
                        Auth::user()->isAdmin() ||
                        Auth::user()->isDepartment() ||
                        Auth::user()->isHOA() ||
                        Auth::user()->isGeneralTrainingManager()
                    )),
                TextColumn::make('Application_count')
                    ->label('عدد الطلبات')
                    ->counts('Application')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('institution_id')
                    ->label('المؤسسة التعليمية')
                    ->relationship('institution', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('major_id')
                    ->label('التخصص')
                    ->relationship('major', 'name')
                    ->searchable()
                    ->preload(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()->visible(fn() => Auth::user()?->isAdmin() || Auth::user()?->isGeneralTrainingManager()),
                RestoreAction::make()->visible(fn() => Auth::user()?->isAdmin() || Auth::user()?->isGeneralTrainingManager()),
                ForceDeleteAction::make()->visible(fn() => Auth::user()?->isAdmin() || Auth::user()?->isGeneralTrainingManager()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn() => Auth::user()?->isAdmin() || Auth::user()?->isGeneralTrainingManager()),
                    ForceDeleteBulkAction::make()->visible(fn() => Auth::user()?->isAdmin() || Auth::user()?->isGeneralTrainingManager()),
                    RestoreBulkAction::make()->visible(fn() => Auth::user()?->isAdmin() || Auth::user()?->isGeneralTrainingManager()),
                ]),
            ]);
    }
}

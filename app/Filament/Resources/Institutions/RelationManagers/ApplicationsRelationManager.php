<?php

namespace App\Filament\Resources\Institutions\RelationManagers;

use App\Filament\Resources\Applications\ApplicationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;

class ApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'applications';

    protected static ?string $relatedResource = ApplicationResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('trainee.full_name')
                    ->label('الاسم الكامل')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('trainee.national_id')
                    ->label('رقم الهوية')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('college.name')
                    ->label('الكلية')
                    ->searchable()
                    ->sortable()
                    ->hidden(fn($livewire) => $livewire->ownerRecord instanceof \App\Models\College),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn(int $state): string => \App\Models\Application::getStatusColor($state))
                    ->formatStateUsing(fn(int $state): string => \App\Models\Application::getStatusLabel($state))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الطلب')
                    ->dateTime('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // CreateAction::make(), // Usually we don't create applications from here, but if needed we can uncomment
            ])
            ->actions([
                ViewAction::make()->url(fn($record) => ApplicationResource::getUrl('view', ['record' => $record->id])),
            ])
            ->bulkActions([
                //
            ]);
    }
}

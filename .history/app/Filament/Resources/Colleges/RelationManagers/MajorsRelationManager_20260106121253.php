<?php

namespace App\Filament\Resources\Colleges\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MajorsRelationManager extends RelationManager
{
    protected static string $relationship = 'majors';

    protected static ?string $modelLabel = 'تخصص';
    protected static ?string $pluralModelLabel = 'التخصصات';
    protected static ?string $title = 'التخصصات';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('name')
                    ->label('اسم التخصص')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\TextInput::make('code')
                    ->label('الرمز')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('id')
                    ->label('رقم')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('اسم التخصص')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label('الرمز')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->multiple(),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}

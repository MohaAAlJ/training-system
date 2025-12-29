<?php

namespace App\Filament\Resources\Trainees\RelationManagers;


use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use App\Filament\Resources\Applications\Schemas\ApplicationForm;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User as AppUser;
use App\Filament\Resources\Applications\Tables\ApplicationsTable;

class ApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'Application';

    public function form(Schema $schema): Schema
    {
        return ApplicationForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return ApplicationsTable::configure($table)
            ->modifyQueryUsing(
                fn(Builder $query) => $query
                    ->withoutGlobalScopes([
                        SoftDeletingScope::class,
                    ])
            );
    }
}

<?php

namespace App\Filament\Resources\Administratives;

use App\Filament\Resources\Administratives\Pages\CreateAdministratives;
use App\Filament\Resources\Administratives\Pages\EditAdministratives;
use App\Filament\Resources\Administratives\Pages\ListAdministratives;
use App\Filament\Resources\Administratives\Pages\ViewAdministratives;
use App\Filament\Resources\Administratives\Schemas\AdministrativesForm;
use App\Filament\Resources\Administratives\Schemas\AdministrativesInfolist;
use App\Filament\Resources\Administratives\Tables\AdministrativesTable;
use App\Models\Administratives;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AdministrativesResource extends Resource
{
    protected static ?string $model = Administratives::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AdministrativesForm::configure($schema)

        ;
    }

    public static function infolist(Schema $schema): Schema
    {
        return AdministrativesInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
            
        return AdministrativesTable::configure($table)
            ;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdministratives::route('/'),
            'create' => CreateAdministratives::route('/create'),
            'view' => ViewAdministratives::route('/{record}'),
            'edit' => EditAdministratives::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

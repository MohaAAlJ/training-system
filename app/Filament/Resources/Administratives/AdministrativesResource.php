<?php

namespace App\Filament\Resources\Administratives;

use App\Filament\Resources\Administratives\Pages\CreateAdministratives;
use App\Filament\Resources\Administratives\Pages\EditAdministratives;
use App\Filament\Resources\Administratives\Pages\ListAdministratives;
use App\Filament\Resources\Administratives\Pages\ViewAdministratives;
use App\Filament\Resources\Administratives\RelationManagers\SectionsRelationManager;
use App\Filament\Resources\Administratives\Schemas\AdministrativesForm;
use App\Filament\Resources\Administratives\Schemas\AdministrativesInfolist;
use App\Filament\Resources\Administratives\Tables\AdministrativesTable;
use App\Models\Administrative;
use BackedEnum;
use Filament\Resources\Resource;
use UnitEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AdministrativesResource extends Resource
{
    protected static ?string $model = Administrative::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;
    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::BuildingLibrary; //filled icon when active
    protected static string | UnitEnum | null $navigationGroup = 'إدارة المتدربين';
    public static function form(Schema $schema): Schema
    {
        return AdministrativesForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AdministrativesInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {

        return AdministrativesTable::configure($table);
    }
    // Arabic labels
    protected static ?string $modelLabel = 'الإدارة';
    protected static ?string $pluralModelLabel = 'الإدارة';
    protected static ?string $navigationLabel = 'الإدارة';
    protected static ?int $navigationSort = 1;

    public static function getRelations(): array
    {
        return [
            SectionsRelationManager::class,
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

<?php

namespace App\Filament\Resources\Administratives;

use App\Filament\Resources\Administratives\Pages\CreateAdministrative;
use App\Filament\Resources\Administratives\Pages\EditAdministrative;
use App\Filament\Resources\Administratives\Pages\ListAdministratives;
use App\Filament\Resources\Administratives\Pages\ViewAdministrative;
use App\Filament\Resources\Administratives\RelationManagers\SectionRelationManager;
use App\Filament\Resources\Administratives\Schemas\AdministrativeForm;
use App\Filament\Resources\Administratives\Schemas\AdministrativeInfolist;
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

class AdministrativeResource extends Resource
{
    protected static ?string $model = Administrative::class;
    protected static ?string $slug = 'administrative';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;
    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::BuildingLibrary; //filled icon when active
    public static function form(Schema $schema): Schema
    {
        return AdministrativeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AdministrativeInfolist::configure($schema);
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
            SectionRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdministratives::route('/'),
            'create' => CreateAdministrative::route('/create'),
            'view' => ViewAdministrative::route('/{record}'),
            'edit' => EditAdministrative::route('/{record}/edit'),
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

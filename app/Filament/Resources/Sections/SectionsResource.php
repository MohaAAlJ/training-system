<?php

namespace App\Filament\Resources\Sections;

use App\Filament\Resources\Sections\Pages\CreateSections;
use App\Filament\Resources\Sections\Pages\EditSections;
use App\Filament\Resources\Sections\Pages\ListSections;
use App\Filament\Resources\Sections\Pages\ViewSections;
use App\Filament\Resources\Sections\RelationManagers\ApplicationsRelationManager;
use App\Filament\Resources\Sections\Schemas\SectionsForm;
use App\Filament\Resources\Sections\Schemas\SectionsInfolist;
use App\Filament\Resources\Sections\Tables\SectionsTable;
use App\Models\Sections;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class SectionsResource extends Resource
{
    protected static ?string $model = Sections::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;
    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::BuildingOffice;


    // Arabic labels
    protected static ?string $modelLabel = 'القسم';
    protected static ?string $pluralModelLabel = 'الأقسام';
    protected static ?string $navigationLabel = 'الأقسام';
    protected static ?int $navigationSort = 3;

    protected static string | UnitEnum | null $navigationGroup = 'إدارة المتدربين';

    public static function form(Schema $schema): Schema
    {
        return SectionsForm::configure($schema);
    }
    public static function infolist(Schema $schema): Schema
    {
        return SectionsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SectionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ApplicationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSections::route('/'),
            'create' => CreateSections::route('/create'),
            'view' => ViewSections::route('/{record}'),
            'edit' => EditSections::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isDepartment()) {
            $query->where('department_id', $user->department?->id);
            return $query;
        }

        return $query->whereRaw('1 = 0');
    }
}

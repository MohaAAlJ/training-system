<?php

namespace App\Filament\Resources\Departments;

use App\Filament\Resources\Departments\Pages\CreateDepartments;
use App\Filament\Resources\Departments\Pages\EditDepartments;
use App\Filament\Resources\Departments\Pages\ListDepartments;
use App\Filament\Resources\Departments\Pages\ViewDepartments;
use App\Filament\Resources\Departments\RelationManagers\SectionsRelationManager;
use App\Filament\Resources\Departments\Schemas\DepartmentsForm;
use App\Filament\Resources\Departments\Schemas\DepartmentsInfolist;
use App\Filament\Resources\Departments\Tables\DepartmentsTable;
use App\Models\Administrative;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class DepartmentsResource extends Resource
{
    protected static ?string $model = Administrative::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;
    // Arabic labels
    protected static ?string $modelLabel = 'الدائرة';
    protected static ?string $pluralModelLabel = 'الدوائر';
    protected static ?string $navigationLabel = 'الدوائر';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return DepartmentsForm::configure($schema);
    }
    public static function infolist(Schema $schema): Schema
    {
        return DepartmentsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DepartmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [

            SectionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDepartments::route('/'),
            'create' => CreateDepartments::route('/create'),
            'view' => ViewDepartments::route('/{record}'),
            'edit' => EditDepartments::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user->isAdmin()) {
            return $query;
        }

        return $query->whereRaw('1 = 0');
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

<?php

namespace App\Filament\Resources\Department;

use App\Filament\Resources\Department\Pages\CreateDepartments;
use App\Filament\Resources\Department\Pages\EditDepartments;
use App\Filament\Resources\Department\Pages\ListDepartments;
use App\Filament\Resources\Department\Pages\ViewDepartments;
use App\Filament\Resources\Department\RelationManagers\SectionsRelationManager;
use App\Filament\Resources\Department\Schemas\DepartmentForm;
use App\Filament\Resources\Department\Schemas\DepartmentsInfolist;
use App\Filament\Resources\Department\Tables\DepartmentTable;
use App\Models\Department;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;
    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::BuildingOffice2; //filled icon when active
       // Arabic labels
    protected static ?string $modelLabel = 'الدوائر';
    protected static ?string $pluralModelLabel = 'الدوائر';
    protected static ?string $navigationLabel = 'الدوائر';
    protected static ?int $navigationSort = 2;


    public static function form(Schema $schema): Schema
    {
        return DepartmentForm::configure($schema);
    }
    public static function infolist(Schema $schema): Schema
    {
        return DepartmentsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DepartmentTable::configure($table);
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
            'index' => ListDepartment::route('/'),
            'create' => CreateDepartment::route('/create'),
            'view' => ViewDepartment::route('/{record}'),
            'edit' => EditDepartment::route('/{record}/edit'),
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







<?php

namespace App\Filament\Resources\Departments;

use App\Filament\Resources\Departments\Pages\CreateDepartment;
use App\Filament\Resources\Departments\Pages\EditDepartment;
use App\Filament\Resources\Departments\Pages\ListDepartments;
use App\Filament\Resources\Departments\Pages\ViewDepartment;
use App\Filament\Resources\Departments\RelationManagers\SectionsRelationManager;
use App\Filament\Resources\Departments\Schemas\DepartmentForm;
use App\Filament\Resources\Departments\Schemas\DepartmentInfolist;
use App\Filament\Resources\Departments\Tables\DepartmentsTable;
use App\Models\Department;
use BackedEnum;
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
    protected static ?string $slug = 'department';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;
    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::BuildingOffice2; //filled icon when active
    protected static ?string $modelLabel = 'الدوائر';
    protected static ?string $pluralModelLabel = 'الدوائر';
    protected static ?string $navigationLabel = 'الدوائر';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function form(Schema $schema): Schema
    {
        return DepartmentForm::configure($schema);
    }
    public static function infolist(Schema $schema): Schema
    {
        return DepartmentInfolist::configure($schema);
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
            'create' => CreateDepartment::route('/create'),
            'view' => ViewDepartment::route('/{record}'),
            'edit' => EditDepartment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if (!$user->isAdmin()) {
            $query->active()->visible();
        }

        if ($user->isAdmin() || $user->isGeneralTrainingManager() || $user->isMonitor()) {
            return $query;
        }

        if ($user->isAssistantTrainingManager()) {
            return $query->whereIn('id', $user->managedDepartmentIds());
        }

        if ($user->isCollegeSupervisor()) {
            return $query;
        }

        if ($user->isDepartment()) {
            if ($user->department()->active()->doesntExist()) {
                return $query->whereRaw('1 = 0');
            }
            return $query->where('id', $user->department->id);
        }

        if ($user->isMinistry()) {
            if ($user->mohDepartment()->active()->doesntExist()) {
                return $query->whereRaw('1 = 0');
            }
            return $query->where('id', $user->mohDepartment->id);
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

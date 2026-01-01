<?php
namespace App\Filament\Resources\Departments;

use App\Filament\Resources\Departments\Pages\CreateDepartments;
use App\Filament\Resources\Departments\Pages\EditDepartments;
use App\Filament\Resources\Departments\Pages\ListDepartments;
use App\Filament\Resources\Departments\Pages\ViewDepartments;
use App\Filament\Resources\Departments\Schemas\DepartmentsForm;
use App\Filament\Resources\Departments\Schemas\DepartmentsInfolist;
use App\Filament\Resources\Departments\Tables\DepartmentsTable;
use App\Models\Departments;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Departments\RelationManagers\ApplicationsRelationManager;
use Illuminate\Support\Facades\Auth;

class DepartmentsResource extends Resource
{
    protected static ?string $model = Departments::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;
    // Arabic labels
    protected static ?string $modelLabel = 'القسم';
    protected static ?string $pluralModelLabel = 'الأقسام';
    protected static ?string $navigationLabel = 'الأقسام';
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
            ApplicationsRelationManager::class,
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

        // السوبر أدمن يشوف كل الأقسام
        if ($user->isAdmin()) {
            return $query;
        }

        // المدير الإداري يشوف فقط الأقسام التابعة لمديريته
        if ($user->isAdministrative()) {
            
            // 1. فلترة أساسية حسب الإدارة
            $query->where('administrative_id', $user->administrative?->id);

            // 2. إذا بدك تطبق منطق "الطبي" هنا أيضاً (اختياري)
            // يعني المدير الطبي يشوف بس الأقسام الطبية في إدارته
            if ($user->administrative?->is_medical === true) {
                 $query->where('is_medical', true); // تأكد ان عمود is_medical موجود في departments لو بدك تفعل هذا الشرط
            }

            return $query;
        }

        // البقية (رئيس قسم، كلية..) ما بيشوفوا اشي
        return $query->whereRaw('1 = 0');
    }
}

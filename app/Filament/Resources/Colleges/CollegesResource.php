<?php

namespace App\Filament\Resources\Colleges;

use App\Filament\Resources\Colleges\Pages\CreateColleges;
use App\Filament\Resources\Colleges\Pages\EditColleges;
use App\Filament\Resources\Colleges\Pages\ListColleges;
use App\Filament\Resources\Colleges\Pages\ViewColleges;
use App\Filament\Resources\Colleges\Schemas\CollegesForm;
use App\Filament\Resources\Colleges\Schemas\CollegesInfolist;
use App\Filament\Resources\Colleges\Tables\CollegesTable;
use App\Models\College;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CollegesResource extends Resource
{
    protected static ?string $model = College::class;

    protected static string | UnitEnum | null $navigationGroup = 'إدارة المتدربين';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::EyeSlash;
    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::Eye; //filled icon when active
       // Arabic labels

    protected static ?string $modelLabel = 'الكليات';
    protected static ?string $pluralModelLabel = 'الكليات';
    protected static ?string $navigationLabel = 'الكليات';
    protected static ?int $navigationSort = 7;
    public static function form(Schema $schema): Schema
    {
        return CollegesForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CollegesInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CollegesTable::configure($table);
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
            'index' => ListColleges::route('/'),
            'create' => CreateColleges::route('/create'),
            'view' => ViewColleges::route('/{record}'),
            'edit' => EditColleges::route('/{record}/edit'),
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

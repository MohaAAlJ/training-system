<?php

namespace App\Filament\Resources\Colleges;

use App\Filament\Resources\Colleges\Pages\CreateCollege;
use App\Filament\Resources\Colleges\Pages\EditCollege;
use App\Filament\Resources\Colleges\Pages\ListCollege;
use App\Filament\Resources\Colleges\Pages\ViewCollege;
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

    public static function shouldRegisterNavigation(): bool
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        return ! ($user->isMinistry() || $user->isCollegeSupervisor());
    }


    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;
    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::BookOpen; //filled icon when active
    // Arabic labels

    protected static ?string $modelLabel = 'الكليات';
    protected static ?string $pluralModelLabel = 'الكليات';
    protected static ?string $navigationLabel = 'الكليات';
    protected static ?int $navigationSort = 6;
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
            'index' => ListCollege::route('/'),
            'create' => CreateCollege::route('/create'),
            'view' => ViewCollege::route('/{record}'),
            'edit' => EditCollege::route('/{record}/edit'),
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

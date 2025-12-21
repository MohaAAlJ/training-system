<?php

namespace App\Filament\Resources\Sections;

use App\Filament\Resources\Sections\Pages\CreateSections;
use App\Filament\Resources\Sections\Pages\EditSections;
use App\Filament\Resources\Sections\Pages\ListSections;
use App\Filament\Resources\Sections\Pages\ViewSections;
use App\Filament\Resources\Sections\Schemas\SectionsForm;
use App\Filament\Resources\Sections\Tables\SectionsTable;
use App\Models\Sections;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;

class SectionsResource extends Resource
{
    protected static ?string $model = Sections::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    // Arabic labels
    protected static ?string $modelLabel = 'القسم';
    protected static ?string $pluralModelLabel = 'الأقسام';
    protected static ?string $navigationLabel = 'الأقسام';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return SectionsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SectionsTable::configure($table);
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
}

<?php

namespace App\Filament\Resources\Stats;

use App\Filament\Resources\Stats\Pages\CreateStats;
use App\Filament\Resources\Stats\Pages\EditStats;
use App\Filament\Resources\Stats\Pages\ListStats;
use App\Filament\Resources\Stats\Pages\ViewStats;
use App\Filament\Resources\Stats\Schemas\StatsForm;
use App\Filament\Resources\Stats\Schemas\StatsInfolist;
use App\Filament\Resources\Stats\Tables\StatsTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StatsResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $slug = 'stats';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartPie;
    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ChartPie;

    protected static ?string $modelLabel = 'إحصائيات';
    protected static ?string $pluralModelLabel = 'الإحصائيات';
    protected static ?string $navigationLabel = 'الإحصائيات';
    
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return StatsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StatsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StatsTable::configure($table);
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
            'index' => ListStats::route('/'),
            'create' => CreateStats::route('/create'),
            'view' => ViewStats::route('/{record}'),
            'edit' => EditStats::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function canViewAny(): bool
    {
        return true;
    }
}

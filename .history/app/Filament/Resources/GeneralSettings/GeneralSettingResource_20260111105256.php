<?php

namespace App\Filament\Resources\GeneralSettings;

use App\Filament\Resources\GeneralSettings\Pages\CreateGeneralSetting;
use App\Filament\Resources\GeneralSettings\Pages\EditGeneralSetting;
use App\Filament\Resources\GeneralSettings\Pages\ListGeneralSettings;
use App\Filament\Resources\GeneralSettings\Schemas\GeneralSettingForm;
use App\Filament\Resources\GeneralSettings\Tables\GeneralSettingsTable;
use App\Models\GeneralSetting;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GeneralSettingResource extends Resource
{
    protected static ?string $model = GeneralSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrench;
    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::WrenchScrewdriver; //filled icon when active
    protected static ?string $modelLabel = 'الإعدادات العامة';
    protected static ?string $pluralModelLabel = 'الإعدادات العامة';
    protected static ?string $navigationLabel = 'الإعدادات العامة';
    protected static ?int $navigationSort = 11;
    protected static string | UnitEnum | null $navigationGroup = 'الإعدادات';
    public static function form(Schema $schema): Schema
    {
        return GeneralSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GeneralSettingsTable::configure($table);
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
            'index' => ListGeneralSettings::route('/'),
            'create' => CreateGeneralSetting::route('/create'),
            'edit' => EditGeneralSetting::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}

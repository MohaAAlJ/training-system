<?php

namespace App\Filament\Resources\Applications\Status;

use App\Filament\Resources\Applications\Tables\ApplicationsTable;
use App\Filament\Resources\Applications\Schemas\ApplicationForm;
use App\Filament\Resources\Applications\Schemas\ApplicationInfolist;
use App\Filament\Resources\Applications\Status\ConfirmedResource\Pages;
use App\Models\Application;
use BackedEnum;
use UnitEnum;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Applications\ApplicationResource;

class ConfirmedResource extends Resource
{
    protected static ?string $model = Application::class;
    protected static ?string $slug = 'confirmed';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckBadge;
    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::CheckBadge;

    protected static ?string $navigationParentItem = 'الطلبات';

    protected static ?string $modelLabel = 'تأكيد';
    protected static ?string $pluralModelLabel = 'المؤكدة';
    protected static ?string $navigationLabel = 'تأكيد';
    protected static ?int $navigationSort = 3;
    protected static string | UnitEnum | null $navigationGroup = 'إدارة المتدربين';

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->isGeneralTrainingManager());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return ApplicationResource::shouldShowStatusPages();
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }

    public static function form(Schema $schema): Schema
    {
        return ApplicationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ApplicationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApplicationsTable::configure($table, 'تأكيد');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConfirmed::route('/'),
            'view' => Pages\ViewConfirmed::route('/{record}'),
            'edit' => Pages\EditConfirmed::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', Application::STATUS_CONFIRMATION);
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\Applications\Status;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Filament\Resources\Applications\Schemas\ApplicationForm;
use App\Filament\Resources\Applications\Schemas\ApplicationInfolist;
use App\Filament\Resources\Applications\Status\WithdrawnResource\Pages;
use App\Filament\Resources\Applications\Tables\ApplicationsTable;
use App\Models\Application;
use BackedEnum;use UnitEnum;use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WithdrawnResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $slug = 'withdrawn';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnLeft;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ArrowUturnLeft;

    protected static ?string $navigationParentItem = 'الطلبات';

    protected static ?string $modelLabel = 'منسحب';

    protected static ?string $pluralModelLabel = 'المنسحبين';

    protected static ?string $navigationLabel = 'منسحب';

    protected static ?int $navigationSort = 8;

    protected static string|UnitEnum|null $navigationGroup = 'إدارة المتدربين';

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
        return (string) static::getEloquentQuery()->count();
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
        return ApplicationsTable::configure($table, 'منسحب');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWithdrawn::route('/'),
            'view' => Pages\ViewWithdrawn::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', Application::STATUS_DROPPED);
    }
}

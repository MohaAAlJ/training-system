<?php

namespace App\Filament\Resources\Applications\Status;

use App\Filament\Resources\Applications\Tables\ApplicationsTable;
use App\Filament\Resources\Applications\Schemas\ApplicationForm;
use App\Filament\Resources\Applications\Schemas\ApplicationInfolist;
use App\Filament\Resources\Applications\Status\RejectedResource\Pages;
use App\Models\Application;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RejectedResource extends Resource
{
    protected static ?string $model = Application::class;
    protected static ?string $slug = 'rejected';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedXCircle;
    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::XCircle;

    protected static ?string $navigationParentItem = 'الطلبات';

    protected static ?string $modelLabel = 'مرفوض';
    protected static ?string $pluralModelLabel = 'المرفوضة';
    protected static ?string $navigationLabel = 'مرفوض';
    protected static ?int $navigationSort = 7;
    protected static string | UnitEnum | null $navigationGroup = 'إدارة المتدربين';

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->isGeneralTrainingManager());
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
        return ApplicationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRejected::route('/'),
            'view' => Pages\ViewRejected::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', Application::STATUS_REJECTED)
            ->onlyTrashed();
    }
}

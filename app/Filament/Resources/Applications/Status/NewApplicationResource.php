<?php

namespace App\Filament\Resources\Applications\Status;

use App\Enums\ApplicationStatus;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Filament\Resources\Applications\Schemas\ApplicationForm;
use App\Filament\Resources\Applications\Schemas\ApplicationInfolist;
use App\Filament\Resources\Applications\Status\NewApplicationResource\Pages;
use App\Filament\Resources\Applications\Tables\ApplicationsTable;
use App\Models\Application;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class NewApplicationResource extends Resource
{
    protected static ?string $model = Application::class;
    protected static ?string $slug = 'new-applications';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-plus';
    protected static string|BackedEnum|null $activeNavigationIcon = 'heroicon-s-document-plus';

    protected static ?string $navigationParentItem = 'الطلبات';

    protected static ?string $modelLabel = 'طلب جديد';
    protected static ?string $pluralModelLabel = 'الطلبات الجديدة';
    protected static ?string $navigationLabel = 'جديد';
    protected static ?int $navigationSort = 1;
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
        return ApplicationsTable::configure($table, 'جديد');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewApplications::route('/'),
            'view' => Pages\ViewNewApplication::route('/{record}'),
            'edit' => Pages\EditNewApplication::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', ApplicationStatus::NEW);
    }
}

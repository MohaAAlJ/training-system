<?php

declare(strict_types=1);

namespace App\Filament\Resources\Applications\Status;

// use App\Enums\ApplicationStatus;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Filament\Resources\Applications\Schemas\ApplicationForm;
use App\Filament\Resources\Applications\Schemas\ApplicationInfolist;
use App\Filament\Resources\Applications\Status\RejectedResource\Pages;
use App\Filament\Resources\Applications\Tables\ApplicationsTable;
use App\Models\Application;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class RejectedResource extends Resource
{
    protected static ?string $model = Application::class;
    protected static ?string $slug = 'rejected-applications';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-x-circle';
    protected static string|BackedEnum|null $activeNavigationIcon = 'heroicon-s-x-circle';

    protected static ?string $navigationParentItem = 'الطلبات';

    protected static ?string $modelLabel = 'طلب مرفوض';
    protected static ?string $pluralModelLabel = 'الطلبات المرفوضة';
    protected static ?string $navigationLabel = 'مرفوض';

    protected static ?int $navigationSort = 7;
    protected static string|UnitEnum|null $navigationGroup = 'إدارة المتدربين';

    public static function canViewAny(): bool
    {
        $user = Auth::user();

        return $user && ($user->isAdmin() || $user->isTrainingManagerLike());
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
        return ApplicationsTable::configure($table, 'مرفوض');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRejectedApplications::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->forUser(Auth::user())
            ->where('status', Application::STATUS_REJECTED);
    }
}

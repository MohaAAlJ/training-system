<?php

namespace App\Filament\Resources\Applications\Status;

use App\Filament\Resources\Applications\Tables\ApplicationsTable;
use App\Filament\Resources\Applications\Schemas\ApplicationForm;
use App\Filament\Resources\Applications\Schemas\ApplicationInfolist;
use App\Filament\Resources\Applications\Status\EndedTrainingResource\Pages;
use App\Models\Application;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EndedTrainingResource extends Resource
{
    protected static ?string $model = Application::class;
    protected static ?string $slug = 'ended-training';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckCircle;
    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::CheckCircle;

    protected static ?string $navigationParentItem = 'الطلبات';

    protected static ?string $modelLabel = 'إنتهى تدريبه';
    protected static ?string $pluralModelLabel = 'المنتهين من التدريب';
    protected static ?string $navigationLabel = 'إنتهى تدريبه';
    protected static ?int $navigationSort = 6;
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
            'index' => Pages\ListEndedTraining::route('/'),
            'view' => Pages\ViewEndedTraining::route('/{record}'),
            'edit' => Pages\EditEndedTraining::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', Application::STATUS_ENDED_TRAINING);
    }
}

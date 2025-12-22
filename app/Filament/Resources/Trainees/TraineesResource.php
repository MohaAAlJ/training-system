<?php

namespace App\Filament\Resources\Trainees;

use App\Filament\Resources\Trainees\Pages\CreateTrainees;
use App\Filament\Resources\Trainees\Pages\EditTrainees;
use App\Filament\Resources\Trainees\Pages\ListTrainees;
use App\Filament\Resources\Trainees\Pages\ViewTrainees;
use App\Filament\Resources\Trainees\RelationManagers\ApplicationsRelationManager;
use App\Filament\Resources\Trainees\Schemas\TraineesForm;
use App\Filament\Resources\Trainees\Schemas\TraineesInfolist;
use App\Filament\Resources\Trainees\Tables\TraineesTable;
use App\Models\Trainees;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class TraineesResource extends Resource
{
    protected static ?string $model = Trainees::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;
    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::Identification; //filled icon when active
    // Arabic labels
    protected static string | UnitEnum | null $navigationGroup = 'إدارة المتدربين';


    protected static ?string $modelLabel = 'المتدرب';
    protected static ?string $pluralModelLabel = 'المتدربين';
    protected static ?string $navigationLabel = 'المتدربين';
    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }
    protected static ?int $navigationSort = 3;


    public static function form(Schema $schema): Schema
    {
        return TraineesForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TraineesInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TraineesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ApplicationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrainees::route('/'),
            'view' => ViewTrainees::route('/{record}'),
            'edit' => EditTrainees::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();


        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isMinistry()) {
            return $query->whereHas('applications', function ($q) {
                $q->where('training_type', \App\Helpers\Constans::TRAINING_TYPE_PRACTICE);
            });
        }

        if ($user->isCollegeSupervisor()) {
            return $query->where('college_id', $user->college?->id);
        }

        if ($user->isDepartmentHead()) {
            return $query->whereHas('applications', function ($q) use ($user) {
                $q->where('department_id', $user->department?->id);
            });
        }

        if ($user->isAdministrative()) {

            $query->whereHas('applications', function ($q) use ($user) {
                $q->whereHas('department', function ($dept) use ($user) {
                    $dept->where('administrative_id', $user->administrative?->id);
                });
            });

            if ($user->administrative?->is_medical === true) {
                $query->whereHas('applications', function ($q) {
                    $q->whereHas('department', function ($dept) {
                        $dept->where('is_medical', true);
                    });
                });
            }

            return $query;
        }

        return $query->whereRaw('1 = 0');
    }
}

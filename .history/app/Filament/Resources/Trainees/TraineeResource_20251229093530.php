<?php

namespace App\Filament\Resources\Trainees;

use App\Models\Application;

use App\Filament\Resources\Trainees\Pages\CreateTrainee;
use App\Filament\Resources\Trainees\Pages\EditTrainee;
use App\Filament\Resources\Trainees\Pages\ListTrainees;
use App\Filament\Resources\Trainees\Pages\ViewTrainee;
use App\Filament\Resources\Trainees\RelationManagers\ApplicationsRelationManager;
use App\Filament\Resources\Trainees\Schemas\TraineeForm;
use App\Filament\Resources\Trainees\Schemas\TraineeInfolist;
use App\Filament\Resources\Trainees\Tables\TraineesTable;
use App\Models\Trainee;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeleingScope;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Constants;

class TraineeResource extends Resource
{
    protected static ?string $model = Trainee::class;
    protected static ?string $slug = 'trainee';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;
    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::Identification; //filled icon when active
    // Arabic labels
    protected static string | UnitEnum | null $navigationGroup = 'إدارة المتدربين';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'المتدرب';
    protected static ?string $pluralModelLabel = 'المتدربين';
    protected static ?string $navigationLabel = 'المتدربين';
    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }


    public static function form(Schema $schema): Schema
    {
        return TraineeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TraineeInfolist::configure($schema);
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
            'create' => CreateTrainee::route('/create'),
            'view' => ViewTrainee::route('/{record}'),
            'edit' => EditTrainee::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();


        if ($user->isAdmin() || $user->isGeneralTrainingManager()) {
            return $query;
        }

        if ($user->isMinistry()) {
            return $query->whereHas('Application', function ($q) {
                $q->where('training_type', Application::TRAINING_TYPE_PRACTICE)
                    ->whereIn('status', [
                        Application::STATUS_INITIAL_APPROVE,
                        Application::STATUS_STARTED_TRAINING,
                        Application::STATUS_ENDED_TRAINING
                    ]);
            });
        }

        if ($user->isCollegeSupervisor()) {
            $collegeId = \App\Models\College::where('user_id', $user->id)->value('id');
            return $query->where('college_id', $collegeId)
                ->whereHas('Application', function ($q) {
                    $q->where('training_type', Application::TRAINING_TYPE_UNIVERSITY)
                        ->whereIn('status', [
                            Application::STATUS_INITIAL_APPROVE,
                            Application::STATUS_STARTED_TRAINING,
                            Application::STATUS_ENDED_TRAINING
                        ]);
                });
        }

        if ($user->isSectionHead()) {
            return $query->whereHas('Application', function ($q) use ($user) {
                $q->where('section_id', $user->Section?->id)
                    ->whereIn('status', [
                        Application::STATUS_STARTED_TRAINING,
                        Application::STATUS_ENDED_TRAINING
                    ]);
            });
        }

        if ($user->isDepartmentHead()) {
            return $query->whereHas('Application', function ($q) use ($user) {
                $q->where('department_id', $user->department?->id)
                    ->whereIn('status', [
                        Application::STATUS_STARTED_TRAINING,
                        Application::STATUS_ENDED_TRAINING
                    ]);
            });
        }

        if ($user->isAdministrative()) {
            return $query->whereHas('Application', function ($q) use ($user) {
                $q->where('administrative_id', $user->administrative?->id)
                    ->whereIn('status', [
                        Application::STATUS_STARTED_TRAINING,
                        Application::STATUS_ENDED_TRAINING
                    ]);
            });
        }

        if ($user->isMedicalManager()) {
            $adminId = \App\Models\Administrative::where('medical_head_user_id', $user->id)->value('id');
            return $query->whereHas('Application', function ($q) use ($adminId) {
                $q->where('administrative_id', $adminId)
                    ->whereHas('department', fn($dept) => $dept->where('is_medical', true))
                    ->whereIn('status', [
                        Application::STATUS_STARTED_TRAINING,
                        Application::STATUS_ENDED_TRAINING
                    ]);
            });
        }

        return $query->whereRaw('1 = 0');
    }
}

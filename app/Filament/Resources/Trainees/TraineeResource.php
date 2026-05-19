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
use Illuminate\Support\Facades\Auth;

class TraineeResource extends Resource
{
    protected static ?string $model = Trainee::class;
    protected static ?string $slug = 'trainee';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;
    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::Identification; //filled icon when active
    // Arabic labels
    protected static string | UnitEnum | null $navigationGroup = 'إدارة المتدربين';

    protected static ?int $navigationSort = 1;

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

        if ($user->isAdmin() || $user->isGeneralTrainingManager() || $user->isMonitor()) {
            return $query->withCount('applications')->latest();
        }

        if ($user->isAssistantTrainingManager()) {
            return $query->whereHas('applications', fn($q) => $q->forUser($user))
                ->withCount(['applications' => fn($q) => $q->forUser($user)])
                ->latest();
        }

        if ($user->isMinistry()) {
            if ($user->mohDepartment) {
                if ($user->mohDepartment()->active()->doesntExist()) {
                    return $query->whereRaw('1 = 0');
                }
                return $query->whereHas('applications', function ($q) use ($user) {
                    $q->where('training_type', Application::PRACTICE)
                        ->whereHas('section', fn($sq) => $sq->whereHas('departments', fn($d) => $d->where('departments.id', $user->mohDepartment->id)->visible()))
                        ->whereIn('status', [
                            Application::STATUS_INITIAL_APPROVE,
                            Application::STATUS_CONFIRMATION,
                            Application::STATUS_WAITING_LIST,
                            Application::STATUS_STARTED_TRAINING,
                            Application::STATUS_ENDED_TRAINING
                        ]);
                })->withCount(['applications' => function ($q) use ($user) {
                    $q->where('training_type', Application::PRACTICE)
                        ->whereHas('section', fn($sq) => $sq->whereHas('departments', fn($d) => $d->where('departments.id', $user->mohDepartment->id)->visible()))
                        ->whereIn('status', [
                            Application::STATUS_INITIAL_APPROVE,
                            Application::STATUS_CONFIRMATION,
                            Application::STATUS_WAITING_LIST,
                            Application::STATUS_STARTED_TRAINING,
                            Application::STATUS_ENDED_TRAINING
                        ]);
                }]);
            }

            return $query->whereHas('applications', function ($q) {
                $q->where('training_type', Application::PRACTICE)
                    ->whereIn('status', [
                        Application::STATUS_INITIAL_APPROVE,
                        Application::STATUS_CONFIRMATION,
                        Application::STATUS_WAITING_LIST,
                        Application::STATUS_STARTED_TRAINING,
                        Application::STATUS_ENDED_TRAINING
                    ]);
            })->withCount(['applications' => function ($q) {
                $q->where('training_type', Application::PRACTICE)
                    ->whereIn('status', [
                        Application::STATUS_INITIAL_APPROVE,
                        Application::STATUS_CONFIRMATION,
                        Application::STATUS_WAITING_LIST,
                        Application::STATUS_STARTED_TRAINING,
                        Application::STATUS_ENDED_TRAINING
                    ]);
            }])->latest();
        }

        if ($user->isCollegeSupervisor()) {
            $college = \App\Models\College::where('user_id', $user->id)->first();
            if (!$college || $user->college()->active()->doesntExist()) {
                return $query->whereRaw('1 = 0');
            }
            return $query->whereHas('applications', function ($q) use ($college) {
                $q->where('college_id', $college->id)
                    ->where('training_type', Application::UNIVERSITY)
                    ->whereIn('status', [
                        Application::STATUS_INITIAL_APPROVE,
                        Application::STATUS_CONFIRMATION,
                        Application::STATUS_WAITING_LIST,
                        Application::STATUS_STARTED_TRAINING,
                        Application::STATUS_ENDED_TRAINING
                    ]);
            })->withCount(['applications' => function ($q) use ($college) {
                $q->where('college_id', $college->id)
                    ->where('training_type', Application::UNIVERSITY)
                    ->whereIn('status', [
                        Application::STATUS_INITIAL_APPROVE,
                        Application::STATUS_CONFIRMATION,
                        Application::STATUS_WAITING_LIST,
                        Application::STATUS_STARTED_TRAINING,
                        Application::STATUS_ENDED_TRAINING
                    ]);
            }])->latest();
        }

        // Section Head: Filter by section
        if ($user->isSectionHead()) {
            if ($user->section()->active()->doesntExist()) {
                return $query->whereRaw('1 = 0');
            }
            return $query->whereHas('applications', function ($q) use ($user) {
                $q->where('section_id', $user->section->id)
                    ->whereIn('status', [
                        Application::STATUS_WAITING_LIST,
                        Application::STATUS_STARTED_TRAINING,
                        Application::STATUS_ENDED_TRAINING
                    ]);
            })->withCount(['applications' => function ($q) use ($user) {
                $q->where('section_id', $user->section->id)
                    ->whereIn('status', [
                        Application::STATUS_WAITING_LIST,
                        Application::STATUS_STARTED_TRAINING,
                        Application::STATUS_ENDED_TRAINING
                    ]);
            }])->latest();
        }

        // Department Head: Filter by department
        if ($user->isDepartmentHead()) {
            if ($user->department()->active()->doesntExist()) {
                return $query->whereRaw('1 = 0');
            }
            return $query->whereHas('applications', function ($q) use ($user) {
                $q->whereHas('section', fn($sq) => $sq->whereHas('departments', fn($d) => $d->where('departments.id', $user->department->id)->visible()))
                    ->whereIn('status', [
                        Application::STATUS_WAITING_LIST,
                        Application::STATUS_STARTED_TRAINING,
                        Application::STATUS_ENDED_TRAINING
                    ]);
            })->withCount(['applications' => function ($q) use ($user) {
                $q->whereHas('section', fn($sq) => $sq->whereHas('departments', fn($d) => $d->where('departments.id', $user->department->id)->visible()))
                    ->whereIn('status', [
                        Application::STATUS_WAITING_LIST,
                        Application::STATUS_STARTED_TRAINING,
                        Application::STATUS_ENDED_TRAINING
                    ]);
            }])->latest();
        }

        if ($user->isAdministrative()) {
            if ($user->administrative()->active()->doesntExist()) {
                return $query->whereRaw('1 = 0');
            }
            return $query->whereHas('applications', function ($q) use ($user) {
                $q->whereHas('section', fn($sq) => $sq->where('administrative_id', $user->administrative->id))
                    ->whereIn('status', [
                        Application::STATUS_WAITING_LIST,
                        Application::STATUS_STARTED_TRAINING,
                        Application::STATUS_ENDED_TRAINING
                    ]);
            })->withCount(['applications' => function ($q) use ($user) {
                $q->whereHas('section', fn($sq) => $sq->where('administrative_id', $user->administrative->id))
                    ->whereIn('status', [
                        Application::STATUS_WAITING_LIST,
                        Application::STATUS_STARTED_TRAINING,
                        Application::STATUS_ENDED_TRAINING
                    ]);
            }])->latest();
        }

        if ($user->isMedicalManager()) {
            $admin = \App\Models\Administrative::where('medical_head_user_id', $user->id)->active()->first();
            if (!$admin) {
                return $query->whereRaw('1 = 0');
            }
            return $query->whereHas('applications', function ($q) use ($admin) {
                $q->whereHas(
                    'section',
                    fn($sq) =>
                    $sq->where('administrative_id', $admin->id)
                        ->whereHas('departments', fn($dept) => $dept->where('is_medical', true)->visible())
                )
                    ->whereIn('status', [
                        Application::STATUS_WAITING_LIST,
                        Application::STATUS_STARTED_TRAINING,
                        Application::STATUS_ENDED_TRAINING
                    ]);
            })->withCount(['applications' => function ($q) use ($admin) {
                $q->whereHas(
                    'section',
                    fn($sq) =>
                    $sq->where('administrative_id', $admin->id)
                        ->whereHas('departments', fn($dept) => $dept->where('is_medical', true)->visible())
                )
                    ->whereIn('status', [
                        Application::STATUS_WAITING_LIST,
                        Application::STATUS_STARTED_TRAINING,
                        Application::STATUS_ENDED_TRAINING
                    ]);
            }])->latest();
        }

        return $query->whereRaw('1 = 0');
    }
}

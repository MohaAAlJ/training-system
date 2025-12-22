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
    // Arabic labels
    protected static ?string $modelLabel = 'المتدرب';
    protected static ?string $pluralModelLabel = 'المتدربين';
    protected static ?string $navigationLabel = 'المتدربين';
    protected static ?int $navigationSort = 1;


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

        // 1. System Admin & General Training Manager: View All
        if ($user->isAdmin() || $user->isGeneralTrainingManager()) {
            return $query;
        }

        // 2. Ministry (MOH): View ONLY Trainees with "Professional" applications
        if ($user->isMinistry()) {
            return $query->whereHas('applications', function ($q) {
                $q->where('training_type', \App\Helpers\Constans::TRAINING_TYPE_PROFESSIONAL);
            });
        }

        // 3. College Supervisor: View ONLY Trainees from their college (Implies University Training)
        if ($user->isCollegeSupervisor()) {
            return $query->where('college_id', $user->college?->id);
            // Optional: If you want to strictly enforce they have an application of type University:
            /*
            return $query->where('college_id', $user->college?->id)
                         ->whereHas('applications', function ($q) {
                             $q->where('training_type', \App\Helpers\Constans::TRAINING_TYPE_UNIVERSITY);
                         });
            */
        }

        // 4. Department Manager (HOD): View trainees who have applications in their department
        if ($user->isDepartmentHead()) {
            return $query->whereHas('applications', function ($q) use ($user) {
                $q->where('department_id', $user->department?->id);
            });
        }

        // 5. Administrative Manager (HOA): View trainees in their administrative unit
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

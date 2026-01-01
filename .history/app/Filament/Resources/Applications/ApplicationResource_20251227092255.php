<?php

namespace App\Filament\Resources\Applications;

use App\Filament\Resources\Applications\Pages\CreateApplication;
use App\Filament\Resources\Applications\Pages\EditApplication;
use App\Filament\Resources\Applications\Pages\ListApplications;
use App\Filament\Resources\Applications\Pages\ViewApplication;
use App\Filament\Resources\Applications\Schemas\ApplicationForm;
use App\Filament\Resources\Applications\Schemas\ApplicationInfolist;
use App\Filament\Resources\Applications\Tables\ApplicationsTable;
use App\Models\Application;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Constants;


class ApplicationResource extends Resource
{

    protected static ?string $model = Application::class;
    protected static ?string $slug = 'application';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboard;
    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::ClipboardDocumentList; //filled icon when active
    // Arabic labels
    protected static ?string $modelLabel = 'طلب';
    protected static ?string $pluralModelLabel = 'الطلبات';
    protected static ?string $navigationLabel = 'الطلبات';
    protected static ?int $navigationSort = 1;
    protected static string | UnitEnum | null $navigationGroup = 'إدارة المتدربين';

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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApplications::route('/'),
            'create' => CreateApplication::route('/create'),
            'view' => ViewApplication::route('/{record}'),
            'edit' => EditApplication::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user->isAdmin() || $user->isGeneralTrainingManager()) {
            return $query;
        }

        if ($user->isCollegeSupervisor()) {
            $collegeId = \App\Models\College::where('user_id', $user->id)->value('id');
            return $query->where('training_type', Application::TRAINING_TYPE_UNIVERSITY)
                ->whereIn('status', [
                    Application::STATUS_INITIAL_APPROVE,
                    Application::STATUS_STARTED_TRAINING,
                    Application::STATUS_ENDED_TRAINING
                ])
                ->whereHas('trainee', function ($q) use ($collegeId) {
                    $q->where('college_id', $collegeId);
                });
        }

        if ($user->isSectionHead()) {
            return $query->where('section_id', $user->Section?->id)
                ->whereIn('status', [
                    Application::STATUS_STARTED_TRAINING,
                    Application::STATUS_ENDED_TRAINING
                ]);
        }

        if ($user->isAdministrative()) {
            return $query->where('administrative_id', $user->administrative?->id)
                ->whereIn('status', [
                    Application::STATUS_STARTED_TRAINING,
                    Application::STATUS_ENDED_TRAINING
                ]);
        }

        if ($user->isMedicalManager()) {
            $adminId = \App\Models\Administrative::where('medical_head_user_id', $user->id)->value('id');
            return $query->where('administrative_id', $adminId)
                ->whereHas('department', fn($q) => $q->where('is_medical', true))
                ->whereIn('status', [
                    Application::STATUS_STARTED_TRAINING,
                    Application::STATUS_ENDED_TRAINING
                ]);
        }

        if ($user->isDepartment()) {
            $query->where('department_id', $user->department?->id)
                ->whereIn('status', [
                    Application::STATUS_STARTED_TRAINING,
                    Application::STATUS_ENDED_TRAINING
                ]);

            if ($user->department?->is_medical === true) {
                $query->whereHas('department', function ($q) {
                    $q->where('is_medical', true);
                });
            }

            return $query;
        }

        if ($user->isMinistry()) {
            return $query->where('training_type', Application::TRAINING_TYPE_PRACTICE)
                ->whereIn('status', [
                    Application::STATUS_INITIAL_APPROVE,
                    Application::STATUS_STARTED_TRAINING,
                    Application::STATUS_ENDED_TRAINING
                ]);
        }

        return $query->whereRaw('1 = 0');
    }
}

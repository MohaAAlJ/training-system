<?php

namespace App\Filament\Resources\Applications;

use app\Filament\Resources\Applications\Pages\CreateApplications;
use App\Filament\Resources\Applications\Pages\EditApplications;
use App\Filament\Resources\Applications\Pages\ListApplications;
use App\Filament\Resources\Applications\Pages\ViewApplications;
use App\Filament\Resources\Applications\Schemas\ApplicationsForm;
use App\Filament\Resources\Applications\Schemas\ApplicationsInfolist;
use App\Filament\Resources\Applications\Tables\ApplicationsTable;
use App\Models\Applications;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Constans;


class ApplicationsResource extends Resource
{

    protected static ?string $model = Applications::class;

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
        return ApplicationsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ApplicationsInfolist::configure($schema);
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
            'create' => CreateApplications::route('/create'),
            'view' => ViewApplications::route('/{record}'),
            'edit' => EditApplications::route('/{record}/edit'),
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
            return $query->where('training_type', Constans::TRAINING_TYPE_UNIVERSITY)
                ->whereIn('status', [
                    Constans::STATUS_STRATED_TRAINING,
                    Constans::STATUS_ENDED_TRAINING
                ])
                ->whereHas('trainee', function ($q) use ($collegeId) {
                    $q->where('college_id', $collegeId);
                });
        }

        if ($user->isSectionHead()) {
            return $query->where('section_id', $user->sections?->id)
                ->whereIn('status', [
                    Constans::STATUS_STRATED_TRAINING,
                    Constans::STATUS_ENDED_TRAINING
                ]);
        }

        if ($user->isAdministrative()) {
            return $query->where('administrative_id', $user->administrative?->id)
                ->whereIn('status', [
                    Constans::STATUS_STRATED_TRAINING,
                    Constans::STATUS_ENDED_TRAINING
                ]);
        }

        if ($user->isDepartment()) {
            $query->where('department_id', $user->department?->id)
                ->whereIn('status', [
                    Constans::STATUS_STRATED_TRAINING,
                    Constans::STATUS_ENDED_TRAINING
                ]);

            if ($user->department?->is_medical === true) {
                $query->whereHas('department', function ($q) {
                    $q->where('is_medical', true);
                });
            }

            return $query;
        }

        if ($user->isMinistry()) {
            return $query->where('training_type', Constans::TRAINING_TYPE_PRACTICE)
                ->whereIn('status', [
                    Constans::STATUS_STRATED_TRAINING,
                    Constans::STATUS_ENDED_TRAINING,
                    Constans::STATUS_INITIAL_APPROVE
                ]);
        }

        return $query->whereRaw('1 = 0');
    }
}

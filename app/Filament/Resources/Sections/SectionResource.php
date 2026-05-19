<?php

namespace App\Filament\Resources\Sections;

use App\Filament\Resources\Sections\Pages\CreateSection;
use App\Filament\Resources\Sections\Pages\EditSection;
use App\Filament\Resources\Sections\Pages\ListSections;
use App\Filament\Resources\Sections\Pages\ViewSection;
use App\Filament\Resources\Sections\RelationManagers\ApplicationsRelationManager;
use App\Filament\Resources\Sections\Schemas\SectionForm;
use App\Filament\Resources\Sections\Schemas\SectionInfolist;
use App\Filament\Resources\Sections\Tables\SectionsTable;
use App\Models\Section;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SectionResource extends Resource
{
    protected static ?string $model = Section::class;
    protected static ?string $slug = 'section';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;
    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::BuildingOffice;


    // Arabic labels
    protected static ?string $modelLabel = 'القسم';
    protected static ?string $pluralModelLabel = 'الأقسام';
    protected static ?string $navigationLabel = 'الأقسام';
    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function form(Schema $schema): Schema
    {
        return SectionForm::configure($schema);
    }
    public static function infolist(Schema $schema): Schema
    {
        return SectionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SectionsTable::configure($table);
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
            'index' => ListSections::route('/'),
            'create' => CreateSection::route('/create'),
            'view' => ViewSection::route('/{record}'),
            'edit' => EditSection::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        $query->withRegisteredCount();

        if (!$user->isAdmin()) {
            $query
                ->active()
                ->whereHas('departments', fn($q) => $q->visible())
                ->with(['departments' => fn($q) => $q->visible()]);
        }

        if ($user->isAdmin() || $user->isGeneralTrainingManager() || $user->isMonitor()) {
            return $query;
        }

        if ($user->isAssistantTrainingManager()) {
            return $query->whereHas('departments', fn($q) => $q->whereIn('departments.id', $user->managedDepartmentIds())->visible());
        }

        if ($user->isDepartment()) {
            if ($user->department()->active()->visible()->doesntExist()) {
                return $query->whereRaw('1 = 0');
            }
            return $query->whereHas('departments', fn($q) => $q->where('departments.id', $user->department->id)->visible());
        }

        if ($user->isAdministrative()) {
            if ($user->administrative()->active()->doesntExist()) {
                return $query->whereRaw('1 = 0');
            }
            return $query->where('administrative_id', $user->administrative->id);
        }

        if ($user->isMedicalManager()) {
            $admin = \App\Models\Administrative::where('medical_head_user_id', $user->id)->active()->first();
            if (!$admin) {
                return $query->whereRaw('1 = 0');
            }
            return $query->where('administrative_id', $admin->id)
                ->whereHas('departments', fn($q) => $q->where('is_medical', true)->visible());
        }

        return $query->whereRaw('1 = 0');
    }
}

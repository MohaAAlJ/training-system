<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Section\Pages\CreateSection;
use App\Filament\Resources\Section\Pages\EditSection;
use App\Filament\Resources\Section\Pages\ListSection;
use App\Filament\Resources\Section\Pages\ViewSection;
use App\Filament\Resources\Section\RelationManagers\ApplicationRelationManager;
use App\Filament\Resources\Section\Schemas\SectionForm;
use App\Filament\Resources\Section\Schemas\SectionInfolist;
use App\Filament\Resources\Section\Tables\SectionTable;
use App\Models\Section;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

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
        return SectionTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ApplicationRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSection::route('/'),
            'create' => CreateSection::route('/create'),
            'view' => ViewSection::route('/{record}'),
            'edit' => EditSection::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user->isAdmin() || $user->isGeneralTrainingManager()) {
            return $query;
        }

        if ($user->isDepartment()) {
            return $query->where('department_id', $user->department?->id);
        }

        if ($user->isAdministrative()) {
            return $query->where('administrative_id', $user->administrative?->id);
        }

        if ($user->isMedicalManager()) {
            $adminId = \App\Models\Administrative::where('medical_head_user_id', $user->id)->value('id');
            return $query->where('administrative_id', $adminId)
                ->whereHas('department', fn($q) => $q->where('is_medical', true));
        }

        return $query->whereRaw('1 = 0');
    }
}

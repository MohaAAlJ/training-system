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
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;


class ApplicationsResource extends Resource
{
    protected static ?string $model = Applications::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    // Arabic labels
    protected static ?string $modelLabel = 'طلب';
    protected static ?string $pluralModelLabel = 'الطلبات';
    protected static ?string $navigationLabel = 'الطلبات';
    protected static ?int $navigationSort = 3;

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

        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isCollegeSupervisor()) {
            return $query->whereHas('trainee', function ($q) use ($user) {
                $q->where('college_id', $user->college?->id);
            });
        }

        if ($user->isDepartmentHead()) {
            return $query->where('department_id', $user->department?->id);
        }

        if ($user->isAdministrative()) {

            $query->whereHas('department', function ($q) use ($user) {
                $q->where('administrative_id', $user->administrative?->id);
            });

            if ($user->administrative?->is_medical === true) {
                 $query->whereHas('department', function ($q) {
                    $q->where('is_medical', true);
                });
            }

            return $query;
        }

        if ($user->isMinistry()) {
             return $query; 
        }

        return $query->whereRaw('1 = 0');
    }
}

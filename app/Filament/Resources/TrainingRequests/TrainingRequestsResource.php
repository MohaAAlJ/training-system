<?php

namespace App\Filament\Resources\TrainingRequests;

use App\Filament\Resources\TrainingRequests\Pages\CreateTrainingRequests;
use App\Filament\Resources\TrainingRequests\Pages\EditTrainingRequests;
use App\Filament\Resources\TrainingRequests\Pages\ListTrainingRequests;
use App\Filament\Resources\TrainingRequests\Pages\ViewTrainingRequests;
use App\Filament\Resources\TrainingRequests\Schemas\TrainingRequestsForm;
use App\Filament\Resources\TrainingRequests\Schemas\TrainingRequestsInfolist;
use App\Filament\Resources\TrainingRequests\Tables\TrainingRequestsTable;
use App\Models\TrainingRequests;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TrainingRequestsResource extends Resource
{
    protected static ?string $model = TrainingRequests::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return TrainingRequestsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TrainingRequestsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingRequestsTable::configure($table);
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
            'index' => ListTrainingRequests::route('/'),
            'create' => CreateTrainingRequests::route('/create'),
            'view' => ViewTrainingRequests::route('/{record}'),
            'edit' => EditTrainingRequests::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

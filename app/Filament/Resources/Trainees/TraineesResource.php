<?php

namespace App\Filament\Resources\Trainees;

use App\Filament\Resources\Trainees\Pages\CreateTrainees;
use App\Filament\Resources\Trainees\Pages\EditTrainees;
use App\Filament\Resources\Trainees\Pages\ListTrainees;
use App\Filament\Resources\Trainees\Pages\ViewTrainees;
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

class TraineesResource extends Resource
{
    protected static ?string $model = Trainees::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrainees::route('/'),
            'create' => CreateTrainees::route('/create'),
            'view' => ViewTrainees::route('/{record}'),
            'edit' => EditTrainees::route('/{record}/edit'),
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

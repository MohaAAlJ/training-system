<?php

namespace App\Filament\Resources\Administratives;

use App\Filament\Resources\Administratives\Pages\CreateAdministratives;
use App\Filament\Resources\Administratives\Pages\EditAdministratives;
use App\Filament\Resources\Administratives\Pages\ListAdministratives;
use App\Filament\Resources\Administratives\Pages\ViewAdministratives;
use App\Filament\Resources\Administratives\RelationManagers\SectionsRelationManager; // Updated import

// ...

    public static function getRelations(): array
    {
        return [
            SectionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdministratives::route('/'),
            'create' => CreateAdministratives::route('/create'),
            'view' => ViewAdministratives::route('/{record}'),
            'edit' => EditAdministratives::route('/{record}/edit'),
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

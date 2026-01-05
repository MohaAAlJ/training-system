<?php

namespace App\Filament\Resources\Stats\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class StatsTable
{
    public static function configure(Table $table): Table
    {
        return $table
        ->query(\App\Models\User::query()->whereRaw('0 = 1'))
        ->columns([])
        ->emptyStateHeading('')        // Empty heading
        ->emptyStateDescription('')    // Empty description
        ->emptyStateIcon(null)         // Remove the X icon
        ->emptyStateActions([])        // Remove any buttons
        ->paginated(false)
        ->striped();                   // Add striped styling to hide empty state better
    }
}

<?php

namespace App\Filament\Resources\Stats\Tables;

use Filament\Tables\Table;
use App\Models\User;

class StatsTable
{
    public static function configure(Table $table): Table
    {
        return $table
        ->query(User::query()->whereRaw('0 = 1'))
        ->columns([])
        ->emptyStateHeading('')        // Empty heading
        ->emptyStateDescription('')    // Empty description
        ->emptyStateIcon(null)         // Remove the X icon
        ->emptyStateActions([])        // Remove any buttons
        ->striped();                   // Add striped styling to hide empty state better
    }
}

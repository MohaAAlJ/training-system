<?php

namespace App\Filament\Widgets;

use App\Models\Administrative;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AdministrativeCapacityOverview extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 1;
    protected static ?string $heading = 'السعة الاستيعابية للإدارات';

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->isGeneralTrainingManager());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Administrative::query()
                    ->active() // Only active admin units
                    ->with(['sections' => fn($q) => $q->active()])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الإدارة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('capacity_stats')
                    ->label('الاستيعاب')
                    ->state(function (Administrative $record) {
                        $stats = $record->getCapacityStats();
                        return $stats['used'] . ' / ' . $stats['total'];
                    }),
                Tables\Columns\TextColumn::make('saturation')
                    ->label('نسبة الإشغال')
                    ->badge()
                    ->color(function (Administrative $record) {
                        $stats = $record->getCapacityStats();
                        $percentage = $stats['total'] > 0 ? ($stats['used'] / $stats['total']) * 100 : 0;
                        if ($percentage >= 90) return 'danger';
                        if ($percentage >= 75) return 'warning';
                        return 'success';
                    })
                    ->state(function (Administrative $record) {
                        $stats = $record->getCapacityStats();
                        $percentage = $stats['total'] > 0 ? ($stats['used'] / $stats['total']) * 100 : 0;
                        return number_format($percentage, 1) . '%';
                    }),
                Tables\Columns\TextColumn::make('available')
                    ->label('المتاح')
                    ->state(function (Administrative $record) {
                        $stats = $record->getCapacityStats();
                        return $stats['available'];
                    })
                    ->color(fn($state) => $state <= 0 ? 'danger' : 'success')
                    ->sortable(query: function (Builder $query, string $direction) {
                        // Approximate sort by raw capacity sum if needed, but computed sort is complex.
                        // For now we disable direct DB sort on this computed column or use a simple join if crucial.
                        // Leaving simplified for MVP.
                        return $query;
                    }),
            ])
            ->paginated(false); // Show all active admin units (usually < 20)
    }
}

<?php

namespace App\Filament\Resources\GeneralSettings\Widgets;

use App\Models\Institution;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class InstitutionsStatusWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'حالة المؤسسات (Institutions Status)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Institution::query()
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المؤسسة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('مفعّلة (Active)')
                    ->onColor('success')
                    ->offColor('danger'),
            ])
            ->paginated([5, 10, 25]);
    }
}

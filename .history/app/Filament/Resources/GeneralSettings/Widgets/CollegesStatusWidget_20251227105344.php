<?php

namespace App\Filament\Resources\GeneralSettings\Widgets;

use App\Models\College;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Filters\SelectFilter;

class CollegesStatusWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'حالة الكليات (Colleges Status)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                College::query()
            )
            ->columns([
                Tables\Columns\TextColumn::make('institution.name')
                    ->label('المؤسسة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الكلية')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('مفعّلة (Active)')
                    ->onColor('success')
                    ->offColor('danger'),
            ])
            ->filters([
                SelectFilter::make('institution_id')
                    ->label('تصفية حسب المؤسسة')
                    ->relationship('institution', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->paginated([5, 10, 25, 50]);
    }
}

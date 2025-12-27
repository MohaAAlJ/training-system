<?php

namespace App\Filament\Resources\GeneralSettings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class GeneralSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('hoa_can_edit_section')
                    ->label('تحرير HOA')
                    ->boolean(),
                IconColumn::make('hoa_can_enable_section')
                    ->label('تفعيل HOA')
                    ->boolean(),
                IconColumn::make('dept_head_can_edit_section')
                    ->label('تحرير القسم')
                    ->boolean(),
                IconColumn::make('dept_head_can_enable_section')
                    ->label('تفعيل القسم')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                //
            ])
            ->paginated(false);
    }
}

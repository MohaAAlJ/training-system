<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use TrainingSettings;
use App\Filament\Pages\icons\IconColumn;
use App\Filament\Pages\columns\TextColumn;

class ManageFooter extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = TrainingSettings::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(components: [
                
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
                //
            ]);
    }
}

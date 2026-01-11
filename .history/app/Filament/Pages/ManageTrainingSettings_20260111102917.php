<?php

namespace App\Filament\Pages;

use App\Settings\TrainingSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Toggle;

class ManageTrainingSettings extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'اعدادات التدريب';
    
    protected static string $settings = TrainingSettings::class; // Link to your class

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('اعدادات العامة')
                    ->schema([
                        Toggle::make('is_public_form_enabled')
                            ->label('Enable Public Form'),
                        Toggle::make('hide_full_sections')
                            ->label('Hide Full Sections'),
                    ])->columns(2),

                Section::make('Permissions')
                    ->schema([
                        Toggle::make('hoa_can_edit_section')->label('HOA Edit Section'),
                        Toggle::make('hoa_can_enable_section')->label('HOA Enable Section'),
                        Toggle::make('dept_head_can_edit_section')->label('Dept Head Edit Section'),
                        Toggle::make('dept_head_can_enable_section')->label('Dept Head Enable Section'),
                    ])->columns(2),

                Section::make('Training Types')
                    ->schema([
                        Toggle::make('enable_training_type_practice')->label('Enable Practice Training'),
                        Toggle::make('enable_training_type_university')->label('Enable University Training'),
                        Toggle::make('can_university_reapply')->label('Allow University Re-application'),
                        Toggle::make('can_practice_reapply')->label('Allow Practice Re-application'),
                    ])->columns(2),
            ]);
    }
}

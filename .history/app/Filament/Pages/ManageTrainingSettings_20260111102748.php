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
    protected static ?string $navigationLabel = 'Training Settings';

    protected static string $settings = TrainingSettings::class; // Link to your class

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('General Configuration')
                    ->schema([
                        Forms\Components\Toggle::make('is_public_form_enabled')
                            ->label('Enable Public Form'),
                        Forms\Components\Toggle::make('hide_full_sections')
                            ->label('Hide Full Sections'),
                    ])->columns(2),

                Forms\Components\Section::make('Permissions')
                    ->schema([
                        Forms\Components\Toggle::make('hoa_can_edit_section')->label('HOA Edit Section'),
                        Forms\Components\Toggle::make('hoa_can_enable_section')->label('HOA Enable Section'),
                        Forms\Components\Toggle::make('dept_head_can_edit_section')->label('Dept Head Edit Section'),
                        Forms\Components\Toggle::make('dept_head_can_enable_section')->label('Dept Head Enable Section'),
                    ])->columns(2),

                Forms\Components\Section::make('Training Types')
                    ->schema([
                        Forms\Components\Toggle::make('enable_training_type_practice')->label('Enable Practice Training'),
                        Forms\Components\Toggle::make('enable_training_type_university')->label('Enable University Training'),
                        Forms\Components\Toggle::make('can_university_reapply')->label('Allow University Re-application'),
                        Forms\Components\Toggle::make('can_practice_reapply')->label('Allow Practice Re-application'),
                    ])->columns(2),
            ]);
    }
}

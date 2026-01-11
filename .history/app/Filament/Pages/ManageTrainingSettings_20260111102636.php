<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageTrainingSettings extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Training Settings';

    protected static string $settings = GeneralSettings::class; // Link to your class

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Configuration')
                    ->schema([
                        Forms\Components\Toggle::make('is_public_form_enabled')
                            ->label('Enable Public Form'),
                        Forms\Components\Toggle::make('hide_full_sections')
                            ->label('Hide Full Sections'),
                    ])->columns(2),

                Forms\Components\Section::make('Permissions')
                    ->schema([
                        Forms\Components\Toggle::make('hoa_can_edit_section')->label('HOA Edit Section'),
                        Forms\Components\Toggle::make('dept_head_can_edit_section')->label('Dept Head Edit Section'),
                        // ... Add all other toggles here
                    ])->columns(2),
            ]);
    }
}
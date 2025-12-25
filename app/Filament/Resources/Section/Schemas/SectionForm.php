<?php

namespace App\Filament\Resources\Section\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name_location')
                    ->label('اسم القسم والموقع')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('administrative_id')
                    ->label('الإدارة')
                    ->relationship('administrative', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('department_id')
                    ->label('القسم')
                    ->relationship('department', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('user_id')
                    ->label('المسؤول')
                    ->relationship('user', 'name', function ($query, $get) {
                        return $query->where('role', \App\Models\User::ROLE_SECTION)
                            ->free($get('user_id'));
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('total_capacity')
                    ->label('السعة الكلية')
                    ->numeric()
                    ->minValue(1)
                    ->default(10)
                    ->rules([
                        fn ($record) => function (string $attribute, $value, \Closure $fail) use ($record) {
                            if ($record && $record->exists) {
                                $used = \App\Models\Application::where('section_id', $record->id)
                                    ->where('status', \App\Models\Application::STATUS_STARTED_TRAINING)
                                    ->count();

                                if ($value < $used) {
                                    $fail("لا يمكن تقليل السعة الكلية ({$value}) عن العدد المستخدم حالياً ({$used}).");
                                }
                            }
                        },
                    ])
                    ->required(),
                Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }
}







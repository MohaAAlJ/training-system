<?php

namespace App\Filament\Resources\Colleges;

use App\Filament\Resources\Colleges\Pages\EditCollege;
use App\Filament\Resources\Colleges\Pages\ViewCollege;
use App\Filament\Resources\Colleges\RelationManagers\MajorsRelationManager;
use App\Filament\Resources\Colleges\Schemas\CollegeForm;
use App\Filament\Resources\Colleges\Schemas\CollegeInfolist;
use App\Models\College;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CollegeResource extends Resource
{
    protected static ?string $model = College::class;
    protected static ?string $slug = 'colleges';

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $modelLabel = 'الكلية';
    protected static ?string $pluralModelLabel = 'الكليات';

    public static function form(Schema $schema): Schema
    {
        // Re-using the logic from the previously deleted CollegeForm if available,
        // or just implementing it here if I can't find it.
        // I'll check if I still have the previous view_file content for CollegeForm.php.
        // It was at app/Filament/Resources/Colleges/Schemas/CollegeForm.php
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('معلومات الكلية')
                    ->columns(2)
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('name')
                            ->label('اسم الكلية')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        \Filament\Forms\Components\Select::make('institution_id')
                            ->label('الجامعة / المؤسسة')
                            ->relationship('institution', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        \Filament\Forms\Components\Select::make('user_id')
                            ->label('مشرف الكلية')
                            ->options(fn($record) => \App\Models\User::getHeadOptions(\App\Models\User::ROLE_COLLEGE, $record?->user_id))
                            ->disableOptionWhen(fn($value, $record) => !\App\Models\User::where('id', $value)->free($record?->user_id)->exists())
                            ->searchable()
                            ->preload()
                            ->helperText('المستخدم الذي سيقوم بإدارة شؤون هذه الكلية في النظام')
                            ->columnSpanFull(),
                        \Filament\Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
                        \Filament\Forms\Components\Toggle::make('Can_add_Application')
                            ->label('السماح بإضافة طلبات')
                            ->default(true),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('معلومات الكلية')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('name')
                            ->label('اسم الكلية'),
                        \Filament\Infolists\Components\TextEntry::make('institution.name')
                            ->label('الجامعة التابعة لها'),
                        \Filament\Infolists\Components\TextEntry::make('user.name')
                            ->label('مشرف الكلية')
                            ->placeholder('لم يتم التعيين'),
                    ])->columns(3),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            MajorsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'view' => ViewCollege::route('/{record}'),
            'edit' => EditCollege::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

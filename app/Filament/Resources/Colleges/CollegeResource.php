<?php

namespace App\Filament\Resources\Colleges;

use App\Filament\Resources\Colleges\Pages\EditCollege;
use App\Filament\Resources\Colleges\Pages\ViewCollege;
use App\Filament\Resources\Colleges\RelationManagers\MajorsRelationManager;
use App\Models\College;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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

    public static function getUrl(string|null $name = null, array $parameters = [], bool $isAbsolute = true, string|null $panel = null, \Illuminate\Database\Eloquent\Model|null $tenant = null, bool $shouldGuessMissingParameters = false, ?string $configuration = null): string
    {
        if ($name === 'index' || $name === null) {
            return \App\Filament\Resources\Institutions\InstitutionResource::getUrl('index', $parameters, $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters, $configuration);
        }

        return parent::getUrl($name, $parameters, $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters, $configuration);
    }

    public static function form(Schema $schema): Schema
    {
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
                            ->label('المؤسسة التعليمية')
                            ->relationship('institution', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        \Filament\Forms\Components\Select::make('user_id')
                            ->label('مشرف الكلية')
                            ->relationship('user', 'name', fn($query) => $query->where('role', \App\Models\User::ROLE_COLLEGE))
                            ->createOptionForm([
                                \Filament\Forms\Components\TextInput::make('name')
                                    ->label('الاسم')
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('user_name')
                                    ->label('اسم المستخدم')
                                    ->required()
                                    ->unique('users', 'user_name'),
                                \Filament\Forms\Components\TextInput::make('email')
                                    ->label('البريد الإلكتروني')
                                    ->required()
                                    ->email()
                                    ->unique('users', 'email'),
                                \Filament\Forms\Components\TextInput::make('password')
                                    ->label('كلمة المرور')
                                    ->password()
                                    ->revealable()
                                    ->required(),
                                \Filament\Forms\Components\Hidden::make('role')
                                    ->default(\App\Models\User::ROLE_COLLEGE),
                            ])
                            ->searchable()
                            ->preload()
                            ->helperText('المستخدم الذي سيقوم بإدارة شؤون هذه الكلية في النظام')
                            ->columnSpanFull()
                            ->nullable(),
                        \Filament\Forms\Components\Toggle::make('active')
                            ->label('نشط')
                            ->onIcon('heroicon-m-check-circle')
                            ->offIcon('heroicon-m-x-circle')
                            ->onColor('success')
                            ->offColor('danger')
                            ->default(true),
                        \Filament\Forms\Components\Toggle::make('add_application')
                            ->label('السماح بإضافة طلبات')
                            ->onIcon('heroicon-m-check-circle')
                            ->offIcon('heroicon-m-x-circle')
                            ->onColor('success')
                            ->offColor('danger')
                            ->default(true),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
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
                        \Filament\Infolists\Components\TextEntry::make('active')
                            ->label('الحالة')
                            ->badge()
                            ->color(fn(int $state): string => \App\Enums\GeneralConst::getStatusColor($state))
                            ->formatStateUsing(fn(int $state): string => \App\Enums\GeneralConst::getStatusLabel($state)),
                    ])->columns(3),
                \Filament\Schemas\Components\Section::make('معلومات النظام')
                    ->description('تواريخ الإنشاء والتحديث')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->icon('heroicon-o-calendar')
                            ->dateTime('d/m/Y - h:i A')
                            ->since()
                            ->badge()
                            ->color('success'),

                        \Filament\Infolists\Components\TextEntry::make('updated_at')
                            ->label('آخر تحديث')
                            ->icon('heroicon-o-arrow-path')
                            ->dateTime('d/m/Y - h:i A')
                            ->since()
                            ->badge()
                            ->color('warning'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            MajorsRelationManager::class,
            \App\Filament\Resources\Institutions\RelationManagers\ApplicationsRelationManager::class,
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

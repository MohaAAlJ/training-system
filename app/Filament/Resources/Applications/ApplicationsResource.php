<?php

namespace App\Filament\Resources\Applications;

use App\Filament\Resources\Applications\Pages\CreateApplications;
use App\Filament\Resources\Applications\Pages\EditApplications;
use App\Filament\Resources\Applications\Pages\ListApplications;
use App\Filament\Resources\Applications\Pages\ViewApplications;
use App\Models\Applications;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
<<<<<<< Updated upstream
=======
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Fieldset;
>>>>>>> Stashed changes

class ApplicationsResource extends Resource
{
    protected static ?string $model = Applications::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

<<<<<<< Updated upstream
    public static function form(Schema $schema): Schema
    {
        return ApplicationsForm::configure($schema);
=======
    protected static ?string $modelLabel = 'طلب';
    protected static ?string $pluralModelLabel = 'الطلبات';
    protected static ?string $navigationLabel = 'الطلبات';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('المعلومات الشخصية')
                    ->schema([
                        TextInput::make('full_name')
                            ->label('الاسم الكامل')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone_number')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->required(),
                        TextInput::make('address')
                            ->label('العنوان')
                            ->maxLength(255),
                    ])->columns(2),

                Fieldset::make('معلومات التعليم')
                    ->schema([
                        TextInput::make('institution_name')
                            ->label('اسم المؤسسة التعليمية')
                            ->required(),
                        TextInput::make('major')
                            ->label('التخصص')
                            ->required(),
                        TextInput::make('major_level')
                            ->label('المستوى الدراسي'),
                    ])->columns(2),

                Fieldset::make('تفاصيل الطلب')
                    ->schema([
                        TextInput::make('position_applied')
                            ->label('الوظيفة المتقدم لها'),
                        DatePicker::make('start_date')
                            ->label('تاريخ البدء')
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('تاريخ الانتهاء')
                            ->required(),
                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'pending' => 'قيد الانتظار',
                                'approved' => 'مقبول',
                                'rejected' => 'مرفوض',
                            ])
                            ->default('pending')
                            ->required(),
                    ])->columns(2),

                Fieldset::make('المستندات')
                    ->schema([
                        Textarea::make('cover_letter')
                            ->label('خطاب التقديم')
                            ->rows(4)
                            ->columnSpanFull(),
                        FileUpload::make('resume_path')
                            ->label('السيرة الذاتية')
                            ->directory('resumes')
                            ->acceptedFileTypes(['application/pdf'])
                            ->columnSpanFull(),
                        Textarea::make('reason_for_rejection')
                            ->label('سبب الرفض')
                            ->rows(3)
                            ->columnSpanFull()
                            ->visible(fn ($get) => $get('status') === 'rejected'),
                    ]),
            ]);
>>>>>>> Stashed changes
    }

    public static function infolist(Schema $schema): Schema
    {
        return ApplicationsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApplicationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApplications::route('/'),
            'create' => CreateApplications::route('/create'),
            'view' => ViewApplications::route('/{record}'),
            'edit' => EditApplications::route('/{record}/edit'),
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

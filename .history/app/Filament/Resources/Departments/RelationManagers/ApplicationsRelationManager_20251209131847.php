<?php

namespace App\Filament\Resources\Departments\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'applications';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('معلومات المتدرب')
                    ->schema([
                        Select::make('trainee_id')
                            ->label('المتدرب')
                            ->relationship('trainee', 'full_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('national_id')
                                    ->label('رقم الهوية')
                                    ->required(),
                                TextInput::make('full_name')
                                    ->label('الاسم الكامل')
                                    ->required(),
                                TextInput::make('phone_number')
                                    ->label('رقم الهاتف')
                                    ->tel()
                                    ->required(),
                                DatePicker::make('dob')
                                    ->label('تاريخ الميلاد')
                                    ->required()
                                    ->native(false),
                                TextInput::make('address')
                                    ->label('العنوان'),
                                Select::make('institution_id')
                                    ->label('المؤسسة التعليمية')
                                    ->relationship('institution', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('institution_major_id')
                                    ->label('التخصص')
                                    ->relationship('institutionMajor', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ])->columns(1),

                Fieldset::make('تفاصيل الطلب')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('تاريخ البدء')
                            ->required()
                            ->native(false),
                        DatePicker::make('end_date')
                            ->label('تاريخ الانتهاء')
                            ->required()
                            ->native(false)
                            ->afterOrEqual('start_date'),
                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'pending' => 'قيد الانتظار',
                                'approved' => 'مقبول',
                                'rejected' => 'مرفوض',
                                'completed' => 'مكتمل',
                            ])
                            ->default('pending')
                            ->required(),
                    ])->columns(2),

                Fieldset::make('المستندات والملاحظات')
                    ->schema([
                        FileUpload::make('letter_image_path')
                            ->label('صورة خطاب التدريب')
                            ->image()
                            ->directory('application-letters')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                        TextInput::make('tags')
                            ->label('الوسوم')
                            ->placeholder('أدخل الوسوم مفصولة بفواصل')
                            ->columnSpanFull(),
                        DatePicker::make('accepted_at')
                            ->label('تاريخ القبول')
                            ->native(false)
                            ->visible(fn ($get) => $get('status') === 'approved'),
                    ])->columns(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')
                    ->searchable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}

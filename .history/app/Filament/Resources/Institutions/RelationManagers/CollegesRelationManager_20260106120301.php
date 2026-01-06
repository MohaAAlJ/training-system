<?php

namespace App\Filament\Resources\Institutions\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CollegesRelationManager extends RelationManager
{
    protected static string $relationship = 'colleges';

    protected static ?string $modelLabel = 'كلية';
    protected static ?string $pluralModelLabel = 'الكليات';
    protected static ?string $title = 'الكليات';

    public function form(Schema $schema): Schema
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('id')
                    ->label('رقم')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('اسم الكلية')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('مشرف الكلية')
                    ->searchable()
                    ->placeholder('غير محدد'),
                ToggleColumn::make('is_active')
                    ->label('الحالة')
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->sortable(),
                ToggleColumn::make('Can_add_Application')
                    ->label('إضافة طلبات')
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->sortable(),
                TextColumn::make('trainees_count')
                    ->label('عدد المتدربين')
                    ->counts('trainees')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
                TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط')
                    ->placeholder('الكل'),
            ])
            ->headerActions([
                CreateAction::make()->visible(fn() => Auth::user()?->isAdmin() ?? false),
            ])
            ->recordActions([
                DeleteAction::make()->visible(fn($record) => !$record->trashed() && Auth::user()?->isAdmin()),
                RestoreAction::make()->visible(fn($record) => $record->trashed() && Auth::user()?->isAdmin()),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn() => Auth::user()?->isAdmin() ?? false),
                    RestoreBulkAction::make()->visible(fn() => Auth::user()?->isAdmin() ?? false),
                ]),
            ])
            ->modifyQueryUsing(
                fn(Builder $query) => $query
                    ->withoutGlobalScopes([
                        SoftDeletingScope::class,
                    ])
            );
    }
}

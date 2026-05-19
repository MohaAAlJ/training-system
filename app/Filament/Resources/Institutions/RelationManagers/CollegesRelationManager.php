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
use Filament\Forms\Components\Hidden;
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
                            ->relationship('user', 'name')
                            ->options(fn($record) => \App\Models\User::getHeadOptions(\App\Models\User::ROLE_COLLEGE, $record?->user_id))
                            ->disableOptionWhen(fn($value, $record) => !\App\Models\User::where('id', $value)->free($record?->user_id)->exists())
                            ->searchable()
                            ->preload()
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
                                Hidden::make('role')
                                    ->default(\App\Models\User::ROLE_COLLEGE)
                                    ->required(),
                            ])
                            ->helperText('المستخدم الذي سيقوم بإدارة شؤون هذه الكلية في النظام')
                            ->columnSpanFull()
                            ->nullable(),
                        \Filament\Forms\Components\Toggle::make('active')
                            ->onIcon('heroicon-m-check-circle')
                            ->offIcon('heroicon-m-x-circle')
                            ->onColor('success')
                            ->offColor('danger')
                            ->label('نشط')
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
                ToggleColumn::make('active')
                    ->label('الحالة')
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->sortable(),
                ToggleColumn::make('add_application')
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
                TernaryFilter::make('active')
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
                DeleteAction::make()
                    ->visible(fn($record) => !$record->trashed() && Auth::user()?->isAdmin())
                    ->before(function ($record, \Filament\Actions\DeleteAction $action) {
                        if ($record->majors()->exists()) {
                            \Filament\Notifications\Notification::make()
                                ->title('لا يمكن الأرشفة')
                                ->body('لا يمكن أرشفة هذا السجل لوجود تخصصات مرتبطة به.')
                                ->danger()
                                ->send();
                            $action->halt();
                        }
                        // Also check for trainees directly linked to college
                        if ($record->trainees()->exists()) {
                            \Filament\Notifications\Notification::make()
                                ->title('لا يمكن الأرشفة')
                                ->body('لا يمكن أرشفة هذا السجل لوجود متدربين مرتبطين به.')
                                ->danger()
                                ->send();
                            $action->halt();
                        }
                    })
                    ->action(function ($record) {
                        try {
                            $record->delete();
                        } catch (\Illuminate\Database\QueryException $exception) {
                            $errorCode = $exception->errorInfo[1] ?? 0;
                            if ($errorCode == 1451) {
                                \Filament\Notifications\Notification::make()
                                    ->title('لا يمكن الحذف')
                                    ->body('لا يمكن حذف هذا السجل نظرًا لوجود بيانات مرتبطة به.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            throw $exception;
                        }
                    }),
                RestoreAction::make()->visible(fn($record) => $record->trashed() && Auth::user()?->isAdmin()),
                ViewAction::make()
                    ->url(fn($record) => \App\Filament\Resources\Colleges\CollegeResource::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->url(fn($record) => \App\Filament\Resources\Colleges\CollegeResource::getUrl('edit', ['record' => $record])),
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

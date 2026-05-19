<?php

namespace App\Filament\Resources\Departments\RelationManagers;

use App\Models\Section;
use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';
    protected static ?string $title = 'الأقسام';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم القسم')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Select::make('administrative_id')
                    ->label('الإدارة')
                    ->relationship('administrative', 'name', fn($query) => $query->active())
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('user_id')
                    ->label('المسؤول')
                    ->relationship(
                        'user',
                        'name',
                        modifyQueryUsing: fn(Builder $query, ?Section $record) => $query
                            ->where('role', User::ROLE_SECTION)
                            ->free($record?->user_id)
                    )
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('الاسم')
                            ->required(),

                        TextInput::make('user_name')
                            ->label('اسم المستخدم')
                            ->required()
                            ->unique('users', 'user_name'),

                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->required()
                            ->email()
                            ->unique('users', 'email'),

                        TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->revealable()
                            ->required(),

                        Hidden::make('role')
                            ->default(User::ROLE_SECTION)
                            ->required(),

                        Toggle::make('active')
                            ->label('الحالة')
                            ->onIcon('heroicon-m-check-circle')
                            ->offIcon('heroicon-m-x-circle')
                            ->onColor('success')
                            ->offColor('danger')
                            ->default(true),
                    ]),

                TextInput::make('capacity')
                    ->label('السعة الكلية')
                    ->numeric()
                    ->minValue(1)
                    ->default(10)
                    ->required(),

                Toggle::make('active')
                    ->label('الحالة')
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->default(true)
                    ->disabled(fn($record) => ! Auth::user()->can('toggleActive', $record ?? new Section())),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('اسم القسم')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('administrative.name')
                    ->label('الإدارة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('المسؤول')
                    ->searchable(),

                TextColumn::make('capacity')
                    ->label('السعة')
                    ->sortable(),

                TextColumn::make('registered_count')
                    ->label('المسجلين')
                    ->numeric()
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                ToggleColumn::make('active')
                    ->label('الحالة')
                    ->disabled(static fn() => ! Auth::user()?->isAdmin() ?? false)
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->onColor('success')
                    ->offColor('danger'),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('تاريخ الحذف')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('active')
                    ->label('الحالة')
                    ->options([
                        1 => 'نشط',
                        0 => 'غير نشط',
                    ]),

                TrashedFilter::make(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('إرفاق قسم')
                    ->preloadRecordSelect()
                    ->visible(static fn() => Auth::user()?->isAdmin() ?? false),

                CreateAction::make()
                    ->label('إضافة قسم جديد')
                    ->visible(static fn() => Auth::user()?->isAdmin() ?? false),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(static fn() => Auth::user()?->isAdmin() ?? false),

                DetachAction::make()
                    ->visible(static fn() => Auth::user()?->isAdmin() ?? false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->visible(static fn() => Auth::user()?->isAdmin() ?? false),
                ]),
            ])
            ->modifyQueryUsing(
                fn(Builder $query) => $query
                    ->withRegisteredCount()
                    ->withoutGlobalScopes([
                        SoftDeletingScope::class,
                    ])
            );
    }
}

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
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use App\Filament\Resources\Sections\Schemas\SectionForm;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Actions\ViewAction;

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
                Hidden::make('department_id')
                    ->default($this->getOwnerRecord()->id),
                Select::make('administrative_id')
                    ->label('الإدارة')
                    ->relationship('administrative', 'name', fn($query) => $query->active())
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('user_id')
                    ->label('المسؤول')
                    // Filter: Role SECTION and 'free' user (or current user)
                    ->relationship('user', 'name', modifyQueryUsing: fn (Builder $query, ?\App\Models\Section $record) => $query
                        ->where('role', \App\Models\User::ROLE_SECTION)
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
                            ->dehydrateStateUsing(fn ($state) => \Illuminate\Support\Facades\Hash::make($state))
                            ->required(),
                        Hidden::make('role')
                            ->default(\App\Models\User::ROLE_SECTION)
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
                    ->disabled(fn($record) => !Auth::user()->can('toggleActive', $record ?? new \App\Models\Section()))
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
                    ->state(function ($record) {
                        return \App\Models\Application::where('section_id', $record->id)
                            ->whereIn('status', [
                                \App\Models\Application::STATUS_STARTED_TRAINING,
                                \App\Models\Application::STATUS_ENDED_TRAINING,
                            ])
                            ->count();
                    })
                    ->badge()
                    ->color('primary'),
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
                SelectFilter::make('user_id')
                    ->label('المسؤول')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('إضافة قسم')
                    ->visible(static fn() => Auth::user()?->isAdmin() ?? false),
            ])
            ->recordActions([
                EditAction::make()->visible(static fn() => Auth::user()?->isAdmin() ?? false),
                DeleteAction::make()->visible(static fn() => Auth::user()?->isAdmin() ?? false),
                RestoreAction::make()->visible(static fn($record) => (Auth::user()?->isAdmin() ?? false) && $record->trashed()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(static fn() => Auth::user()?->isAdmin() ?? false),
                    RestoreBulkAction::make()->visible(static fn() => Auth::user()?->isAdmin() ?? false),
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

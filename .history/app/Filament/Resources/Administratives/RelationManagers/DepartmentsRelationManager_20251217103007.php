<?php

namespace App\Filament\Resources\Administratives\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DepartmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'departments';

    public function form(Schema $schema): Schema
    {
        
    }
    
}



    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name_location')
                    ->label('اسم القسم والموقع')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('user_id')
                    ->label('المسؤول')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('total_capacity')
                    ->label('السعة الكلية')
                    ->numeric()
                    ->minValue(1)
                    ->default(10)
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name_location')
            ->columns([
                TextColumn::make('name_location')
                    ->label('اسم القسم والموقع')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('المسؤول')
                    ->searchable(),
                TextColumn::make('total_capacity')
                    ->label('السعة')
                    ->sortable(),
                TextColumn::make('registered_count')
                    ->label('المسجلين')
                    ->state(function ($record) {
                        return DB::table('applications')
                            ->where('department_id', $record->id)
                            ->whereIn('status', ['active', 'completed'])
                            ->count();
                    })
                    ->badge()
                    ->color('primary'),
                ToggleColumn::make('status')
                    ->label('الحالة')
                    ->disabled(static fn() => ! Auth::user()?->isAdmin() ?? false)
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->beforeStateUpdated(function ($record, $state) {
                        $record->status = $state ? 'active' : 'inactive';
                        $record->save();
                    }),
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
            ->headerActions([
                CreateAction::make()->visible(static fn() => Auth::user()?->isAdmin() ?? false),
            ])
            ->recordActions([
                EditAction::make()->visible(static fn() => Auth::user()?->isAdmin() ?? false),
                DeleteAction::make()->visible(static fn() => Auth::user()?->isAdmin() ?? false),
                ForceDeleteAction::make()->visible(static fn() => Auth::user()?->isAdmin() ?? false),
                RestoreAction::make()->visible(static fn() => Auth::user()?->isAdmin() ?? false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(static fn() => Auth::user()?->isAdmin() ?? false),
                    ForceDeleteBulkAction::make()->visible(static fn() => Auth::user()?->isAdmin() ?? false),
                    RestoreBulkAction::make()->visible(static fn() => Auth::user()?->isAdmin() ?? false),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
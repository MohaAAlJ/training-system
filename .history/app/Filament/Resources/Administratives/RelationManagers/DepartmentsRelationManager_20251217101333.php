<?php

namespace App\Filament\Resources\Administratives\RelationManagers;

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

// --- تصحيح المسارات (Imports) الهامة جداً ---
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
// -------------------------------------------

class DepartmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'departments';

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
                    // استخدام auth()->user() مباشرة لضمان الدقة
                    ->disabled(fn() => ! auth()->user()->isAdmin()) 
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
            ])
            ->headerActions([
                // الآن ستظهر لأننا استخدمنا الكلاس الصحيح من Tables\Actions
                CreateAction::make()->visible(fn() => auth()->user()->isAdmin()),
            ])
            ->recordActions([
                // أزرار التعديل والحذف ستظهر الآن
                EditAction::make()->visible(fn() => auth()->user()->isAdmin()),
                DeleteAction::make()->visible(fn() => auth()->user()->isAdmin()),
                ForceDeleteAction::make()->visible(fn() => auth()->user()->isAdmin()),
                RestoreAction::make()->visible(fn() => auth()->user()->isAdmin()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn() => auth()->user()->isAdmin()),
                    ForceDeleteBulkAction::make()->visible(fn() => auth()->user()->isAdmin()),
                    RestoreBulkAction::make()->visible(fn() => auth()->user()->isAdmin()),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}
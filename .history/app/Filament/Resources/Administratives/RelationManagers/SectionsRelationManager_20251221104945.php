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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;

class SectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

    protected static ?string $title = 'الاقسام التابعة للادارة';
    protected static ?string $label = 'القسم';
    protected static ?string $pluralLabel = 'الاقسام';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('department_id')
                    ->label('الدائرة (الوظيفية)')
                    ->relationship('department', 'title')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('name_location')
                    ->label('اسم القسم والموقع (مثل: صيدلية الاسعاف - الطابق الارضي)')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Select::make('hos')
                    ->label('المسؤول (رئيس القسم)')
                    ->relationship('hosUser', 'name')
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
                TextColumn::make('department.title')
                    ->label('الدائرة')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name_location')
                    ->label('اسم القسم والموقع')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => 'السعة: ' . $record->total_capacity),

                TextColumn::make('hosUser.name')
                    ->label('المسؤول')
                    ->searchable(),

                TextColumn::make('registered_count')
                    ->label('المسجلين')
                    ->state(function ($record) {
                        return DB::table('applications')
                            ->where('section_id', $record->id)
                            ->whereIn('status', ['active', 'completed']) // Adjust statuses as per Constants
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
                        $record->status = $state ? 'active' : 'inactive'; // Assuming boolean true/false from toggle maps to these strings based on model casts/mutators or direct assignment
                        // Since cast is boolean, $state is boolean. If DB expects string 'active', we must be careful. 
                        // Sections model cast 'status' => 'boolean'.
                        // If model cast is boolean, we should set it to true/false.
                        // But previous code set 'active'/'inactive'.
                        // Let's stick to true/false if cast is boolean.
                        $record->status = $state;
                        $record->save();
                    }),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('department_id')
                    ->label('الدائرة')
                    ->relationship('department', 'title'),

                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        '1' => 'نشط',
                        '0' => 'غير نشط',
                    ]),

                TrashedFilter::make(),
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
            ->modifyQueryUsing(
                fn(Builder $query) => $query
                    ->withoutGlobalScopes([
                        SoftDeletingScope::class,
                    ])
            );
    }
}

<?php

namespace App\Filament\Resources\Colleges\RelationManagers;

use App\Models\Major;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MajorsRelationManager extends RelationManager
{
    protected static string $relationship = 'majors';

    protected static ?string $modelLabel = 'تخصص';
    protected static ?string $pluralModelLabel = 'التخصصات';
    protected static ?string $title = 'التخصصات';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم التخصص')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('اسم التخصص')
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('pivot.active')
                    ->label('نشط')
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->updateStateUsing(function ($record, bool $state) {
                        $this->ownerRecord->majors()->updateExistingPivot($record->id, ['active' => $state]);
                    }),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('إرفاق / إضافة تخصص')
                    ->preloadRecordSelect()
                    ->multiple()
                    ->attachAnother(false)
                    ->recordSelect(
                        fn(Select $select) => $select
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('اسم التخصص')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                // Find or create globally — prevents duplication in majors table
                                $major = Major::firstOrCreate(['name' => $data['name']]);
                                // Link to this college with active=true
                                $this->ownerRecord->majors()->syncWithoutDetaching([
                                    $major->id => ['active' => true],
                                ]);
                                return $major->id;
                            })
                    )
                    ->using(function (array $data): void {
                        // Attach existing selected majors with active=true
                        $ids = collect($data['recordId'] ?? [])
                            ->mapWithKeys(fn($id) => [$id => ['active' => true]])
                            ->toArray();
                        $this->ownerRecord->majors()->syncWithoutDetaching($ids);
                    }),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
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

<?php

namespace App\Filament\Resources\User;

use App\Filament\Resources\User\Pages\CreateUser;
use App\Filament\Resources\User\Pages\EditUser;
use App\Filament\Resources\User\Pages\ListUser;
use App\Filament\Resources\User\Pages\ViewUser;
use App\Filament\Resources\User\Schemas\UserForm;
use App\Filament\Resources\User\Schemas\UserInfolist;
use App\Filament\Resources\User\Tables\UserTable;
use App\Models\User;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $modelLabel = 'المستخدم';
    protected static ?string $pluralModelLabel = 'المستخدمين';
    protected static ?string $navigationLabel = 'المستخدمين';
    protected static ?int $navigationSort = 7;
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;
    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::UserGroup; //filled icon when active

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserTable::configure($table);
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
            'index' => ListUser::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
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

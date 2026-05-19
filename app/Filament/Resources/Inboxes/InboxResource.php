<?php

namespace App\Filament\Resources\Inboxes;

use App\Filament\Resources\Inboxes\Pages\ListInboxes;
use App\Filament\Resources\Inboxes\Pages\ViewInbox;
use App\Filament\Resources\Inboxes\Schemas\InboxForm;
use App\Filament\Resources\Inboxes\Schemas\InboxInfolist;
use App\Filament\Resources\Inboxes\Tables\InboxesTable;
use App\Models\Mailbox;
use BackedEnum;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InboxResource extends Resource
{
    protected static ?string $model = Mailbox::class;

    protected static ?string $modelLabel = 'رسالة واردة';

    protected static ?string $pluralModelLabel = 'صندوق الوارد';

    protected static ?string $navigationLabel = 'صندوق الوارد';

    protected static ?string $slug = 'inbox';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Inbox;

    protected static ?string $recordTitleAttribute = 'subject';

    public static function canAccess(): bool
    {
        return ! Auth::user()->isAdmin();
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('status', Mailbox::STATUS_SENT)
            ->whereHas('recipients', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->with(['recipients' => function ($query) {
                // Only eager-load the current user's pivot row to minimise data transfer
                $query->where('user_id', Auth::id());
            }]);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Auth::user()->unreadMessagesCount();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return InboxForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InboxInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InboxesTable::configure($table);
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
            'index' => ListInboxes::route('/'),
            'view' => ViewInbox::route('/{record}'),
        ];
    }
}

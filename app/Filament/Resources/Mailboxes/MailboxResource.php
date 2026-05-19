<?php

namespace App\Filament\Resources\Mailboxes;

use App\Filament\Resources\Mailboxes\Pages\CreateMailbox;
use App\Filament\Resources\Mailboxes\Pages\EditMailbox;
use App\Filament\Resources\Mailboxes\Pages\ListMailboxes;
use App\Filament\Resources\Mailboxes\Pages\ViewMailbox;
use App\Filament\Resources\Mailboxes\Schemas\MailboxForm;
use App\Filament\Resources\Mailboxes\Schemas\MailboxInfolist;
use App\Filament\Resources\Mailboxes\Tables\MailboxesTable;
use App\Models\Mailbox;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MailboxResource extends Resource
{
    protected static ?string $modelLabel = 'رسالة البريد';

    protected static ?string $pluralModelLabel = 'صندوق البريد';

    protected static ?string $navigationLabel = 'صندوق البريد';

    protected static ?string $model = Mailbox::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Envelope;

    protected static ?string $recordTitleAttribute = 'subject';

    public static function canAccess(): bool
    {
        return \Illuminate\Support\Facades\Auth::user()->isAdmin();
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return $record->status === Mailbox::STATUS_DRAFT;
    }

    public static function form(Schema $schema): Schema
    {
        return MailboxForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MailboxInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MailboxesTable::configure($table);
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
            'index' => ListMailboxes::route('/'),
            'create' => CreateMailbox::route('/create'),
            'view' => ViewMailbox::route('/{record}'),
            'edit' => EditMailbox::route('/{record}/edit'),
        ];
    }
}

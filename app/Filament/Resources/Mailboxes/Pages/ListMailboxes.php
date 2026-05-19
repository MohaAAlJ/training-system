<?php

namespace App\Filament\Resources\Mailboxes\Pages;

use App\Filament\Resources\Mailboxes\MailboxResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class ListMailboxes extends ListRecords
{
    protected static string $resource = MailboxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل')
                ->badge(\App\Models\Mailbox::count()),
            'draft' => Tab::make('المسودات')
                ->modifyQueryUsing(fn(\Illuminate\Database\Eloquent\Builder $query) => $query->drafts())
                ->icon('heroicon-m-document-text')
                ->badge(\App\Models\Mailbox::drafts()->count()),
            'sent' => Tab::make('المرسلة')
                ->modifyQueryUsing(fn(\Illuminate\Database\Eloquent\Builder $query) => $query->sent())
                ->icon('heroicon-m-paper-airplane')
                ->badge(\App\Models\Mailbox::sent()->count())
                ->badgeColor('success'),
        ];
    }
}

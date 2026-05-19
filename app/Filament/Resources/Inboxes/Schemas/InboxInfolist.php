<?php

namespace App\Filament\Resources\Inboxes\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class InboxInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الرسالة')
                    ->description('تفاصيل الرسالة وموعد وتاريخ استلامها')
                    ->icon('heroicon-o-information-circle')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('sender.name')
                                    ->label('المرسل:')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-user'),
                                TextEntry::make('created_at')
                                    ->label('تاريخ وتوقت الاستلام:')
                                    ->dateTime('d/m/Y - h:i A')
                                    ->icon('heroicon-o-calendar')
                                    ->badge()
                                    ->color('gray'),
                            ]),

                        TextEntry::make('subject')
                            ->label('الموضوع:')
                            ->weight('bold')
                            ->size('lg')
                            ->icon('heroicon-o-bars-3-bottom-left')
                            ->columnSpanFull(),
                    ]),

                Section::make('محتوى الرسالة')
                    ->icon('heroicon-o-envelope-open')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('body')
                            ->hiddenLabel()
                            ->html()
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'prose dark:prose-invert max-w-none']),
                    ])
                    ->collapsible(false),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Mailboxes\Schemas;

use App\Models\Mailbox;
use App\Models\User;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MailboxInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── Message Header ────────────────────────────────────────────
                Section::make('معلومات الرسالة')
                    ->description('تفاصيل الرسالة المرسلة')
                    ->icon('heroicon-o-envelope')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('sender.name')
                                ->label('المرسل')
                                ->badge()
                                ->color('info')
                                ->icon('heroicon-o-user'),

                            TextEntry::make('status')
                                ->label('الحالة')
                                ->badge()
                                ->formatStateUsing(fn(int $state): string => match ($state) {
                                    Mailbox::STATUS_DRAFT => 'مسودة',
                                    Mailbox::STATUS_SENT  => 'مرسلة',
                                    default              => 'غير معروف',
                                })
                                ->color(fn(int $state): string => match ($state) {
                                    Mailbox::STATUS_DRAFT => 'warning',
                                    Mailbox::STATUS_SENT  => 'success',
                                    default              => 'gray',
                                })
                                ->icon('heroicon-o-paper-airplane'),

                            TextEntry::make('created_at')
                                ->label('تاريخ الإرسال')
                                ->dateTime('d/m/Y - h:i A')
                                ->badge()
                                ->color('gray')
                                ->icon('heroicon-o-calendar'),
                        ]),

                        Grid::make(2)->schema([
                            TextEntry::make('target_type')
                                ->label('نوع الاستهداف')
                                ->badge()
                                ->formatStateUsing(fn(int $state): string => match ($state) {
                                    Mailbox::TARGET_ALL   => 'الجميع (كل المستخدمين النشطين)',
                                    Mailbox::TARGET_ROLES => 'حسب الأدوار',
                                    Mailbox::TARGET_USER  => 'مستخدم محدد',
                                    default              => 'غير محدد',
                                })
                                ->color(fn(int $state): string => match ($state) {
                                    Mailbox::TARGET_ALL   => 'success',
                                    Mailbox::TARGET_ROLES => 'warning',
                                    Mailbox::TARGET_USER  => 'info',
                                    default              => 'gray',
                                }),

                            TextEntry::make('target_roles')
                                ->label('الأدوار المستهدفة')
                                ->badge()
                                ->color('warning')
                                ->formatStateUsing(function ($state): string {
                                    if (empty($state)) {
                                        return '—';
                                    }
                                    $labels = collect((array) $state)
                                        ->map(fn($role) => User::ROLE_LABELS[(int) $role] ?? "دور {$role}")
                                        ->join('، ');
                                    return $labels;
                                })
                                ->visible(fn($record): bool => $record && (int) $record->target_type === Mailbox::TARGET_ROLES),

                            TextEntry::make('targetUser.name')
                                ->label('المستخدم المستهدف')
                                ->badge()
                                ->color('info')
                                ->icon('heroicon-o-user')
                                ->visible(fn($record): bool => $record && (int) $record->target_type === Mailbox::TARGET_USER),
                        ]),

                        TextEntry::make('recipients_count')
                            ->label('عدد المستلمين')
                            ->state(fn($record) => $record->recipients()->count())
                            ->badge()
                            ->color('primary')
                            ->icon('heroicon-o-users')
                            ->suffix(' مستلم'),

                        TextEntry::make('subject')
                            ->label('عنوان الرسالة')
                            ->weight('bold')
                            ->size('lg')
                            ->icon('heroicon-o-bars-3-bottom-left')
                            ->columnSpanFull(),
                    ]),

                // ── Message Body ──────────────────────────────────────────────
                Section::make('محتوى الرسالة')
                    ->icon('heroicon-o-envelope-open')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('body')
                            ->hiddenLabel()
                            ->html()
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'prose dark:prose-invert max-w-none']),
                    ]),
            ]);
    }
}

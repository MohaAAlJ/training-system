<?php

namespace App\Filament\Resources\Mailboxes\Schemas;

use App\Models\Mailbox;
use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MailboxForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تفاصيل الرسالة')
                    ->description('قم بتحديد الفئة المستهدفة واكتب نص الرسالة')
                    ->icon('heroicon-o-envelope')
                    ->schema([
                        Hidden::make('sender_id')
                            ->default(auth()->id()),

                        Grid::make(1)->schema([
                            Radio::make('target_type')
                                ->label('نوع الاستهداف')
                                ->options([
                                    Mailbox::TARGET_ALL   => 'الجميع (كل المستخدمين النشطين)',
                                    Mailbox::TARGET_ROLES => 'حسب الدور (أدوار متعددة)',
                                    Mailbox::TARGET_USER  => 'مستخدم محدد',
                                ])
                                ->default(Mailbox::TARGET_ALL)
                                ->required()
                                ->live()
                                ->columnSpanFull(),

                            CheckboxList::make('target_roles')
                                ->label('الأدوار المستهدفة')
                                ->options(User::ROLE_LABELS)
                                ->columns(3)
                                ->required()
                                ->visible(fn($get) => (int) $get('target_type') === Mailbox::TARGET_ROLES)
                                ->columnSpanFull(),

                            Select::make('_role_filter')
                                ->label('اختر الدور أولاً')
                                ->options(User::ROLE_LABELS)
                                ->required()
                                ->live()
                                ->native(false)
                                ->searchable()
                                ->dehydrated(false)
                                ->visible(fn($get) => (int) $get('target_type') === Mailbox::TARGET_USER)
                                ->columnSpanFull(),

                            Select::make('target_user_id')
                                ->label('المستخدم المستهدف')
                                ->options(function ($get) {
                                    $role = $get('_role_filter');
                                    if (! $role) {
                                        return [];
                                    }
                                    return User::query()
                                        ->active()
                                        ->where('role', $role)
                                        ->pluck('name', 'id');
                                })
                                ->required()
                                ->searchable()
                                ->native(false)
                                ->live()
                                ->visible(fn($get) => (int) $get('target_type') === Mailbox::TARGET_USER && filled($get('_role_filter')))
                                ->columnSpanFull(),

                            TextInput::make('subject')
                                ->label('عنوان الرسالة')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),

                            RichEditor::make('body')
                                ->label('نص الرسالة')
                                ->required()
                                ->columnSpanFull(),
                        ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Mailboxes\Tables;

use App\Models\Mailbox;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MailboxesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')
                    ->label('عنوان الرسالة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-envelope')
                    ->limit(50)
                    ->tooltip(fn(TextColumn $column): ?string => strlen($column->getState()) > 50 ? $column->getState() : null),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn(int $state): string => match ($state) {
                        Mailbox::STATUS_DRAFT => 'warning',
                        Mailbox::STATUS_SENT  => 'success',
                        default              => 'gray',
                    })
                    ->formatStateUsing(fn(int $state): string => match ($state) {
                        Mailbox::STATUS_DRAFT => 'مسودة',
                        Mailbox::STATUS_SENT  => 'مرسلة',
                        default              => 'غير معروف',
                    })
                    ->sortable(),

                TextColumn::make('sender.name')
                    ->label('المرسل')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-user'),

                TextColumn::make('target_type')
                    ->label('نوع الاستهداف')
                    ->badge()
                    ->color(fn(int $state): string => match ($state) {
                        Mailbox::TARGET_ALL   => 'success',
                        Mailbox::TARGET_ROLES => 'warning',
                        Mailbox::TARGET_USER  => 'info',
                        default              => 'gray',
                    })
                    ->formatStateUsing(fn(int $state): string => match ($state) {
                        Mailbox::TARGET_ALL   => 'الجميع',
                        Mailbox::TARGET_ROLES => 'حسب الأدوار',
                        Mailbox::TARGET_USER  => 'مستخدم محدد',
                        default              => 'غير محدد',
                    })
                    ->sortable(),

                TextColumn::make('recipients_count')
                    ->label('عدد المستلمين')
                    ->counts('recipients')
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-o-users')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإرسال')
                    ->dateTime('d/m/Y - h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('target_type')
                    ->label('نوع الاستهداف')
                    ->options([
                        Mailbox::TARGET_ALL   => 'الجميع',
                        Mailbox::TARGET_ROLES => 'حسب الأدوار',
                        Mailbox::TARGET_USER  => 'مستخدم محدد',
                    ])
                    ->native(false),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label('من تاريخ'),
                        DatePicker::make('created_until')->label('إلى تاريخ'),
                    ])
                    ->query(
                        fn(Builder $query, array $data): Builder => $query
                            ->when($data['created_from'], fn(Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn(Builder $q, $date) => $q->whereDate('created_at', '<=', $date))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = Indicator::make('تاريخ الإرسال من: ' . Carbon::parse($data['created_from'])->toFormattedDateString())
                                ->removeField('created_from');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = Indicator::make('تاريخ الإرسال إلى: ' . Carbon::parse($data['created_until'])->toFormattedDateString())
                                ->removeField('created_until');
                        }
                        return $indicators;
                    }),

                TrashedFilter::make(),
            ])
            ->actions([
                Action::make('send')
                    ->label('إرسال')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->iconButton()
                    ->tooltip('إرسال الآن')
                    ->requiresConfirmation()
                    ->visible(fn(Mailbox $record) => $record->status === Mailbox::STATUS_DRAFT)
                    ->action(function (Mailbox $record) {
                        $userIds = $record->resolveRecipientQuery()->pluck('id')->toArray();

                        if (!empty($userIds)) {
                            $record->recipients()->attach($userIds);
                        }

                        $record->update(['status' => Mailbox::STATUS_SENT]);

                        Notification::make()
                            ->title('تم إرسال الرسالة بنجاح')
                            ->success()
                            ->send();
                    }),

                ViewAction::make()->iconButton()->tooltip('عرض التفاصيل'),

                EditAction::make()
                    ->iconButton()
                    ->tooltip('تعديل')
                    ->color('warning')
                    ->visible(fn(Mailbox $record) => $record->status === Mailbox::STATUS_DRAFT),

                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('حذف')
                    ->visible(fn(Mailbox $record) => $record->status === Mailbox::STATUS_DRAFT),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (Collection $records) {
                            $records->each(function (Mailbox $record) {
                                if ($record->status === Mailbox::STATUS_DRAFT) {
                                    $record->delete();
                                }
                            });
                        }),
                ]),
            ])
            ->emptyStateHeading('لا توجد رسائل')
            ->emptyStateDescription('لم يتم إنشاء أو إرسال أي رسائل حتى الآن.')
            ->emptyStateIcon('heroicon-o-inbox');
    }
}

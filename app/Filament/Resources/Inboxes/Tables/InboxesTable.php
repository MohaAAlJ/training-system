<?php

namespace App\Filament\Resources\Inboxes\Tables;

use App\Models\Mailbox;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InboxesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sender.name')
                    ->label('المرسل')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-user'),

                TextColumn::make('subject')
                    ->label('الموضوع')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(fn(TextColumn $column): ?string => strlen($column->getState()) > 50 ? $column->getState() : null)
                    ->icon(fn(Mailbox $record): string => $record->isReadByUser(Auth::id()) ? 'heroicon-o-envelope-open' : 'heroicon-s-envelope')
                    ->color(fn(Mailbox $record): string => $record->isReadByUser(Auth::id()) ? 'gray' : 'primary')
                    ->weight(fn(Mailbox $record): string => $record->isReadByUser(Auth::id()) ? 'regular' : 'bold'),

                TextColumn::make('body')
                    ->label('الرسالة')
                    ->formatStateUsing(fn(string $state): string => Str::limit(strip_tags($state), 30))
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('d/m/Y - h:i A')
                    ->description(fn(Mailbox $record): string => $record->created_at->diffForHumans())
                    ->sortable(query: fn(Builder $query, string $direction): Builder => $query->orderBy('mailboxes.created_at', $direction)),
            ])
            ->defaultSort('mailboxes.created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_read')
                    ->label('حالة القراءة')
                    ->placeholder('الكل')
                    ->trueLabel('مقروءة')
                    ->falseLabel('غير مقروءة')
                    ->queries(
                        true: fn(Builder $query) => $query->whereHas('recipients', fn($q) => $q->where('user_id', Auth::id())->whereNotNull('read_at')),
                        false: fn(Builder $query) => $query->whereHas('recipients', fn($q) => $q->where('user_id', Auth::id())->whereNull('read_at')),
                        blank: fn(Builder $query) => $query,
                    ),

                TernaryFilter::make('is_deleted')
                    ->label('المحذوفات')
                    ->placeholder('غير المحذوفة')
                    ->trueLabel('المحذوفة')
                    ->falseLabel('الكل')
                    ->queries(
                        true: fn(Builder $query) => $query->whereHas('recipients', fn($q) => $q->where('user_id', Auth::id())->where('mailbox_user.is_deleted', true)),
                        false: fn(Builder $query) => $query,
                        blank: fn(Builder $query) => $query->whereHas('recipients', fn($q) => $q->where('user_id', Auth::id())->where('mailbox_user.is_deleted', false)),
                    ),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label('من تاريخ'),
                        DatePicker::make('created_until')->label('إلى تاريخ'),
                    ])
                    ->query(
                        fn(Builder $query, array $data): Builder => $query
                            ->when($data['created_from'], fn(Builder $q, $date) => $q->whereDate('mailboxes.created_at', '>=', $date))
                            ->when($data['created_until'], fn(Builder $q, $date) => $q->whereDate('mailboxes.created_at', '<=', $date))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = Indicator::make('تاريخ الاستلام من: ' . Carbon::parse($data['created_from'])->toFormattedDateString())
                                ->removeField('created_from');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = Indicator::make('تاريخ الاستلام إلى: ' . Carbon::parse($data['created_until'])->toFormattedDateString())
                                ->removeField('created_until');
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                Action::make('mark_as_read')
                    ->label('تعيين كمقروء')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Mailbox $record): bool => ! $record->isReadByUser(Auth::id()))
                    ->action(fn(Mailbox $record) => Auth::user()->receivedMessages()->updateExistingPivot($record->id, ['read_at' => now()])),

                Action::make('mark_as_unread')
                    ->label('تعيين كغير مقروء')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn(Mailbox $record): bool => $record->isReadByUser(Auth::id()))
                    ->action(fn(Mailbox $record) => Auth::user()->receivedMessages()->updateExistingPivot($record->id, ['read_at' => null])),

                ViewAction::make()->color('primary'),

                Action::make('delete_inbox')
                    ->label('حذف')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn(Mailbox $record) => Auth::user()->receivedMessages()->updateExistingPivot($record->id, ['is_deleted' => true])),
            ])
            ->emptyStateHeading('صندوق الوارد فارغ')
            ->emptyStateDescription('لا توجد لديك أي رسائل واردة جديدة.')
            ->emptyStateIcon('heroicon-o-inbox');
    }
}

<?php

namespace App\Filament\Resources\Applications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('trainee.full_name')
                    ->label('المتدرب')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('trainee.national_id')
                    ->label('رقم الهوية')
                    ->searchable(),
                TextColumn::make('trainee.institution.name')
                    ->label('المؤسسة')
                    ->formatStateUsing(fn ($state) => is_array($state) ? ($state['ar'] ?? $state['en'] ?? reset($state)) : $state)
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('trainee.major.name')
                    ->label('التخصص')
                    ->formatStateUsing(fn ($state) => is_array($state) ? ($state['ar'] ?? $state['en'] ?? reset($state)) : $state)
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('department.name_location')
                    ->label('القسم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('تاريخ البدء')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'info',
                        'waiting' => 'warning',
                        'approved' => 'primary',
                        'active' => 'success',
                        'completed' => 'gray',
                        'rejected' => 'danger',
                        'paused' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pending' => 'طلب جديد',
                                'approved' => 'استيعاب',
                                'waiting' => 'لم يستلم عمل بعد',
                                'active' => 'بدء العمل',
                                'completed' => 'انتهى',
                                'rejected' => 'مرفوض',
                                'paused' => 'منقطع',
                        default => $state,
                    }),
                TextColumn::make('duration')
                    ->label('مدة التدريب (أيام)')
                    ->getStateUsing(fn ($record) => $record->start_date && $record->end_date ? $record->end_date->diffInDays($record->start_date) : '-')
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('end_date', $direction))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('accepted_at')
                    ->label('تاريخ القبول')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tags')
                    ->label('الوسوم')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'طلب جديد',
                        'waiting' => 'استيعاب',
                        'approved' => 'قبول جامعة',
                        'active' => 'بدء العمل',
                        'completed' => 'انتهى',
                        'rejected' => 'مرفوض',
                        'paused' => 'منقطع',
                    ]),
                SelectFilter::make('department_id')
                    ->label('القسم')
                    ->relationship('department', 'name_location')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('trainee_id')
                    ->label('المتدرب')
                    ->relationship('trainee', 'full_name')
                    ->searchable()
                    ->preload(),
                Filter::make('start_date')
                    ->label('نطاق تاريخ البدء')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('start_date_from')
                            ->label('من'),
                        \Filament\Forms\Components\DatePicker::make('start_date_to')
                            ->label('إلى'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['start_date_from'], fn (Builder $q) => $q->whereDate('start_date', '>=', $data['start_date_from']))
                            ->when($data['start_date_to'], fn (Builder $q) => $q->whereDate('start_date', '<=', $data['start_date_to']));
                    }),
                TrashedFilter::make(),
            ])

            ->recordActions([
                ViewAction::make()
                    ->color('info') // تغيير اللون لتمييزه
                    ->outlined(),

                // 1. زر التعديل يظهر فقط للأدمن ورئيس القسم (يختفي عن الكلية)
                EditAction::make()
                    ->color('primary')
                    ->outlined()
                    ->visible(fn () => ! auth()->user()->isCollegeSupervisor()), 

                // 2. زر "تأكيد/تحويل للانتظار" الخاص بمشرف الكلية
                \Filament\Tables\Actions\Action::make('moveToWaiting')
                    ->label('تحويل لقائمة الانتظار')
                    ->icon('heroicon-m-arrow-path') // أيقونة مناسبة
                    ->color('success')
                    ->button() // شكله كزر وليس رابط
                    // يظهر فقط لمشرف الكلية + عندما تكون الحالة Approved
                    ->visible(fn ($record) => 
                        auth()->user()->isCollegeSupervisor() && 
                        $record->status === 'approved'
                    )
                    ->requiresConfirmation() // طلب تأكيد قبل التنفيذ
                    ->modalHeading('تغيير حالة الطلب')
                    ->modalDescription('هل أنت متأكد من تحويل حالة هذا الطالب إلى "لم يستلم عمل بعد" (Waiting)؟')
                    ->action(function ($record) {
                        $record->update(['status' => 'waiting']);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('تم تحديث الحالة بنجاح')
                            ->success()
                            ->send();
                    }),

                DeleteAction::make()->visible(fn() => Auth::user()?->isAdmin() ?? false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn() => Auth::user()?->isAdmin() ?? false),
                    ForceDeleteBulkAction::make()->visible(fn() => Auth::user()?->isAdmin() ?? false),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}


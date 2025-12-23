<?php

namespace App\Filament\Widgets;

use App\Helpers\Constans;
use App\Models\Applications;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Lang;

class GTMRecentApplications extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'أحدث الطلبات المقدمة';

    public static function canView(): bool
    {
        // Visible to GTM (8) and Admin (1)
        return in_array(Auth::user()->role, [Constans::ROLE_GTM, Constans::ROLE_ADMIN]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Applications::query()
                    ->latest('created_at')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('trainee.full_name')
                    ->label('اسم المتدرب'),
                Tables\Columns\TextColumn::make('administrative.title')
                    ->label('مكان التدريب'),
                Tables\Columns\TextColumn::make('department.title')
                    ->label('التخصص'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn($state) => Lang::get('translation.status.' . $state, [], 'ar'))
                    ->badge()
                    ->color(fn(string $state): string => match ((int)$state) {
                        Constans::STATUS_NEW => 'warning',
                        Constans::STATUS_STRATED_TRAINING => 'success',
                        Constans::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التقديم')
                    ->dateTime()
                    ->since(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('initial_approve')
                    ->label('موافقة مبدئية')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn($record) => Auth::user()->isGeneralTrainingManager() && $record->status == Constans::STATUS_NEW)
                    ->requiresConfirmation()
                    ->action(fn($record) => $record->update(['status' => Constans::STATUS_INITIAL_APPROVE])),

                Action::make('start_training')
                    ->label('بدء التدريب')
                    ->color('success')
                    ->icon('heroicon-o-play')
                    ->visible(fn($record) => Auth::user()->isGeneralTrainingManager() && $record->status == Constans::STATUS_WAITING_LIST)
                    ->form([
                        DatePicker::make('start_date')
                            ->label('تاريخ البدء')
                            ->required()
                            ->default(now()),
                        TextInput::make('duration')
                            ->label('المدة (يوم)')
                            ->numeric()
                            ->required()
                            ->default(30),
                    ])
                    ->action(function ($record, array $data) {
                        $startDate = \Carbon\Carbon::parse($data['start_date']);
                        $duration = (int)$data['duration'];
                        $endDate = $startDate->copy()->addDays($duration);

                        $record->update([
                            'status' => Constans::STATUS_STRATED_TRAINING,
                            'start_date' => $startDate,
                            'duration' => $duration,
                            'end_date' => $endDate,
                        ]);
                    }),

                DeleteAction::make()
                    ->label('رفض')
                    ->modalHeading('رفض الطلب')
                    ->modalDescription('هل أنت متأكد من رفض هذا الطلب؟ سيتم نقله إلى قائمة المرفوضات.')
                    ->action(function ($record) {
                        $record->update(['status' => Constans::STATUS_REJECTED]);
                        $record->delete();
                    }),
            ]);
    }
}

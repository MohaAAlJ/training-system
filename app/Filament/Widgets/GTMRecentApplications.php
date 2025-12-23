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
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'تحتاج إجراءات';

    public static function canView(): bool
    {
        // Visible for roles that need to take actions
        return in_array(Auth::user()->role, [
            Constans::ROLE_GTM,
            Constans::ROLE_ADMIN,
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Applications::query()
                    ->whereIn('status', [Constans::STATUS_NEW, Constans::STATUS_WAITING_LIST])
                    ->latest('created_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('trainee.full_name')
                    ->label('اسم المتدرب')
                    ->searchable(),
                Tables\Columns\TextColumn::make('administrative.title')
                    ->label('مكان التدريب'),
                Tables\Columns\TextColumn::make('department.title')
                    ->label('الدائرة'),
                Tables\Columns\TextColumn::make('section.name_location')
                    ->label('القسم'),
                Tables\Columns\TextColumn::make('training_type')
                    ->label('نوع التدريب')
                    ->formatStateUsing(fn ($state) => Constans::TRAINING_TYPES[$state] ?? 'غير محدد'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn($state) => Lang::get('translation.status.' . $state, [], 'ar'))
                    ->badge()
                    ->color(fn(string $state): string => match ((int)$state) {
                        Constans::STATUS_NEW => 'warning',
                        Constans::STATUS_WAITING_LIST => 'primary',
                        Constans::STATUS_STRATED_TRAINING => 'success',
                        Constans::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التقديم')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make(),
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

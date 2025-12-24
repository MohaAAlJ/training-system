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
        $user = Auth::user();
        if (!$user) return false;

        // Visible for: GTM, Admin, College Supervisor, and MOH
        return in_array($user->role, [
            Constans::ROLE_GTM,
            Constans::ROLE_ADMIN,
            Constans::ROLE_COLLEGE,
            Constans::ROLE_MOH,
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Applications::query()
                    ->whereIn('status', [
                        Constans::STATUS_NEW,
                        Constans::STATUS_INITIAL_APPROVE,
                        Constans::STATUS_CONFIRMATION,
                        Constans::STATUS_WAITING_LIST
                    ])
                    ->latest('created_at')
            )
            ->modifyQueryUsing(function ($query) {
                $user = Auth::user();

                if ($user->isAdmin() || $user->isGeneralTrainingManager()) {
                    return $query;
                }

                if ($user->isCollegeSupervisor()) {
                    $collegeId = $user->college?->id;
                    return $query->where('status', Constans::STATUS_INITIAL_APPROVE)
                        ->where('training_type', Constans::TRAINING_TYPE_UNIVERSITY)
                        ->whereHas('trainee', function ($q) use ($collegeId) {
                            $q->where('college_id', $collegeId);
                        });
                }

                if ($user->isMinistry()) {
                    return $query->where('status', Constans::STATUS_INITIAL_APPROVE)
                        ->where('training_type', Constans::TRAINING_TYPE_PRACTICE);
                }

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('trainee.full_name')
                    ->label('اسم المتدرب')
                    ->searchable(),
                Tables\Columns\TextColumn::make('trainee.national_id')
                    ->label('رقم الهوية')
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
                        Constans::STATUS_INITIAL_APPROVE => 'info',
                        Constans::STATUS_CONFIRMATION => 'primary',
                        Constans::STATUS_WAITING_LIST => 'success',
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
                    ->color('info')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn($record) => (Auth::user()->isGeneralTrainingManager() || Auth::user()->isAdmin()) && (int)$record->status === Constans::STATUS_NEW)
                    ->requiresConfirmation()
                    ->successNotificationTitle('تمت الموافقة المبدئية بنجاح')
                    ->action(fn($record) => $record->update(['status' => Constans::STATUS_INITIAL_APPROVE])),

                Action::make('confirm')
                    ->label('تأكيد')
                    ->color('success')
                    ->icon('heroicon-o-check-badge')
                    ->visible(fn($record) =>
                        (int)$record->status === Constans::STATUS_INITIAL_APPROVE &&
                        (Auth::user()->isAdmin() || Auth::user()->isCollegeSupervisor() || Auth::user()->isMinistry())
                    )
                    ->requiresConfirmation()
                    ->successNotificationTitle('تم تأكيد الطلب بنجاح')
                    ->action(fn($record) => $record->update([
                        'status' => Constans::STATUS_CONFIRMATION,
                        'accepted_at' => now(),
                    ])),

                Action::make('final_approve')
                    ->label('اعتماد نهائي')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn($record) =>
                        (Auth::user()->isGeneralTrainingManager() || Auth::user()->isAdmin()) &&
                        (int)$record->status === Constans::STATUS_CONFIRMATION
                    )
                    ->requiresConfirmation()
                    ->successNotificationTitle('تم الاعتماد النهائي بنجاح')
                    ->action(fn($record) => $record->update(['status' => Constans::STATUS_WAITING_LIST])),

                Action::make('start_training')
                    ->label('بدء التدريب')
                    ->color('success')
                    ->icon('heroicon-o-play')
                    ->visible(fn($record) => (Auth::user()->isGeneralTrainingManager() || Auth::user()->isAdmin()) && (int)$record->status === Constans::STATUS_WAITING_LIST)
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
                    ->successNotificationTitle('تم بدء التدريب بنجاح')
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
                    ->visible(fn() => Auth::user()->isAdmin() || Auth::user()->isGeneralTrainingManager())
                    ->action(function ($record) {
                        $record->update(['status' => Constans::STATUS_REJECTED]);
                        $record->delete();
                    }),
            ]);
    }
}

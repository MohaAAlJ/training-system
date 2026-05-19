<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Filament\Resources\Applications\Tables\ApplicationsTable;
use App\Models\Application;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn(Application $record) => 
                    Auth::user()->isAdmin() || Auth::user()->isTrainingManagerLike() ||
                    ((Auth::user()->isCollegeSupervisor() || Auth::user()->isMinistry()) && in_array($record->status, [Application::STATUS_NEW, Application::STATUS_INITIAL_APPROVE, Application::STATUS_CONFIRMATION]))
                ),

            Action::make('initial_approve')
                ->label('موافقة مبدئية')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn(Application $record) => $record->status === Application::STATUS_NEW && (Auth::user()->isTrainingManagerLike() || Auth::user()->isAdmin()))
                ->requiresConfirmation()
                ->action(function (Application $record) {
                    $record->update(['status' => Application::STATUS_INITIAL_APPROVE, 'accepted_at' => now()]);
                    Notification::make()->title('تمت الموافقة المبدئية بنجاح')->success()->send();
                }),

            Action::make('confirm_application')
                ->label('تأكيد الطلب')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn(Application $record) => $record->status === Application::STATUS_INITIAL_APPROVE && (Auth::user()->isMinistry() || Auth::user()->isCollegeSupervisor()))
                ->requiresConfirmation()
                ->action(function (Application $record) {
                    $record->update(['status' => Application::STATUS_CONFIRMATION]);
                    Notification::make()->title('تم تأكيد الطلب بنجاح')->success()->send();
                }),

            Action::make('process_application')
                ->label('معالجة الطلب')
                ->icon('heroicon-o-cpu-chip')
                ->color('primary')
                ->visible(fn(Application $record) => in_array($record->status, [Application::STATUS_CONFIRMATION, Application::STATUS_WAITING_LIST]) && (Auth::user()->isTrainingManagerLike() || Auth::user()->isAdmin()))
                ->form(fn(Application $record) => ApplicationsTable::getProcessApplicationFormSchema($record))
                ->action(fn(Application $record, array $data) => ApplicationsTable::processApplicationAction($record, $data)),

            Action::make('end_training')
                ->label('إنهاء التدريب')
                ->icon('heroicon-o-flag')
                ->color('warning')
                ->visible(fn(Application $record) => $record->status === Application::STATUS_STARTED_TRAINING && (Auth::user()->isTrainingManagerLike() || Auth::user()->isAdmin()))
                ->requiresConfirmation()
                ->action(function (Application $record) {
                    $record->update(['status' => Application::STATUS_ENDED_TRAINING, 'end_date' => now()]);
                    Notification::make()->title('تم إنهاء التدريب بنجاح')->success()->send();
                }),

            Action::make('cancel_training')
                ->label('إلغاء التدريب')
                ->icon('heroicon-o-stop-circle')
                ->color('danger')
                ->visible(fn(Application $record) => $record->status === Application::STATUS_STARTED_TRAINING && (Auth::user()->isTrainingManagerLike() || Auth::user()->isAdmin() || Auth::user()->isCollegeSupervisor() || Auth::user()->isMinistry()))
                ->form([
                    Textarea::make('cancel_reason')
                        ->label('سبب الإلغاء')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (Application $record, array $data) {
                    if (!Auth::user()->can('cancel', $record)) {
                        Notification::make()->title('لا تملك صلاحية إلغاء هذا الطلب')->danger()->send();
                        return;
                    }

                    $daysNote = $record->days_note ?? [];
                    $daysNote['who_cancelled'] = Auth::id();
                    $daysNote['cancel_reason'] = $data['cancel_reason'] ?? null;

                    $record->update([
                        'status' => Application::STATUS_CANCELLED,
                        'days_note' => $daysNote,
                    ]);
                    Notification::make()->title('تم إلغاء طلب التدريب بنجاح')->success()->send();
                }),

            Action::make('reject')
                ->label('رفض الطلب')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn(Application $record) => in_array($record->status, [Application::STATUS_NEW, Application::STATUS_INITIAL_APPROVE, Application::STATUS_CONFIRMATION, Application::STATUS_WAITING_LIST]) && (Auth::user()->isTrainingManagerLike() || Auth::user()->isAdmin()))
                ->requiresConfirmation()
                ->action(function (Application $record) {
                    $record->update(['status' => Application::STATUS_REJECTED]);
                    Notification::make()->title('تم رفض الطلب')->danger()->send();
                }),

            ApplicationsTable::getUploadTraineeFilesAction(),

            ApplicationsTable::getDownloadTraineeFilesAction(),
        ];
    }
}

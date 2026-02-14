<?php

namespace App\Filament\Resources\Applications\Pages;

// use App\Enums\TrainingType;
use App\Models\Application;
use App\Filament\Resources\Applications\ApplicationResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditApplication extends EditRecord
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('حفظ التغييرات')
                ->action('save')
                ->icon('heroicon-m-check')
                ->color('primary')
                ->keyBindings(['mod+s']),
            $this->getCancelFormAction()
                ->label('إلغاء'),
            DeleteAction::make()
                ->label('رفض')
                ->visible(fn() => !$this->getRecord()->trashed() && (Auth::user()?->isAdmin() || Auth::user()?->isGeneralTrainingManager())),
            RestoreAction::make()
                ->visible(fn() => $this->getRecord()->trashed() && (Auth::user()?->isAdmin() || Auth::user()?->isGeneralTrainingManager())),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم تحديث بيانات طلب المتدرب بنجاح';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $trainee = $this->getRecord()->trainee;

        if ($trainee && isset($data['training_type'])) {
            $trainingType = (int)$data['training_type'];

            // If training type is PRACTICE, clear educational fields
            if ($trainingType === Application::PRACTICE) {
                $trainee->update([
                    'institution_id' => null,
                    'college_id' => null,
                    'major_id' => null,
                ]);
            }
            // If training type is UNIVERSITY, update educational fields from form
            elseif ($trainingType === Application::UNIVERSITY) {
                $updateData = [];

                if (isset($data['institution_id'])) {
                    $updateData['institution_id'] = $data['institution_id'];
                }
                if (isset($data['college_id'])) {
                    $updateData['college_id'] = $data['college_id'];
                }
                if (isset($data['major_id'])) {
                    $updateData['major_id'] = $data['major_id'];
                }

                if (!empty($updateData)) {
                    $trainee->update($updateData);
                }
            }
        }

        return $data;
    }
}

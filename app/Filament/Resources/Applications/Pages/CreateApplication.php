<?php

namespace App\Filament\Resources\Applications\Pages;

// use App\Enums\ApplicationStatus;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\Application;
use App\Models\Trainee;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateApplication extends CreateRecord
{
    protected static string $resource = ApplicationResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تمت إضافة الطلب بنجاح';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (Auth::user()->isCollegeSupervisor() || Auth::user()->isMinistry()) {
            $data['status'] = Application::STATUS_CONFIRMATION;
            $data['accepted_at'] = now();

            if (Auth::user()->isCollegeSupervisor()) {
                $data['training_type'] = Application::UNIVERSITY;
            }
        }

        if (empty($data['trainee_id'])) {
            DB::beginTransaction();
            try {
                $collegeId = $data['college_id'] ?? null;
                $institutionId = $data['institution_id'] ?? null;

                if (Auth::user()->isCollegeSupervisor() && (!$collegeId || !$institutionId)) {
                    $collegeObj = \App\Models\College::where('user_id', Auth::user()->id)->first();
                    if ($collegeObj) {
                        $collegeId = $collegeId ?: $collegeObj->id;
                        $institutionId = $institutionId ?: $collegeObj->institution_id;

                        $data['college_id'] = $collegeId;
                        $data['institution_id'] = $institutionId;
                    }
                }

                $traineeData = [
                    'full_name' => $data['full_name'] ?? 'New Trainee',
                    'national_id' => $data['national_id'] ?? null,
                    'phone_number' => $data['phone_number'] ?? null,
                    'governorate_id' => $data['governorate_id'] ?? null,
                    'address' => $data['address'] ?? null,
                    'street' => $data['street'] ?? null,
                    'dob' => $data['dob'] ?? null,
                    'gender' => $data['gender'] ?? null,
                ];

                $trainee = Trainee::create($traineeData);
                $data['trainee_id'] = $trainee->id;

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        } else {
            // Update existing trainee
            DB::beginTransaction();
            try {
                $trainee = Trainee::find($data['trainee_id']);
                if ($trainee) {
                    $trainee->update([
                        'full_name' => $data['full_name'] ?? $trainee->full_name,
                        'phone_number' => $data['phone_number'] ?? $trainee->phone_number,
                        'street' => $data['street'] ?? $trainee->street,
                        'dob' => $data['dob'] ?? $trainee->dob,
                        'gender' => $data['gender'] ?? $trainee->gender,
                    ]);
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        }


        unset(
            $data['full_name'],
            $data['national_id'],
            $data['phone_number'],
            $data['governorate_id'],
            $data['address'],
            $data['street'],
            $data['dob_year'],
            $data['dob_month'],
            $data['dob_day']
        );

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $user = Auth::user();

        if ($user->isCollegeSupervisor()) {
            $traineeId = $data['trainee_id'] ?? null;
            $supervisorCollegeId = $user->college?->id;

            if ($traineeId) {
                $upgradable = Application::where('trainee_id', $traineeId)
                    ->whereIn('status', [
                        Application::STATUS_NEW,
                        Application::STATUS_INITIAL_APPROVE,
                    ])->first();

                if ($upgradable) {
                    if ($upgradable->college_id !== null && $upgradable->college_id !== $supervisorCollegeId) {
                        Notification::make()
                            ->title('خطأ')
                            ->body('هذا المتدرب لديه طلب تدريب مسجل من كلية أخرى.')
                            ->danger()
                            ->send();

                        $this->halt();
                    }

                    $upgradable->update($data);
                    return $upgradable;
                }
            }
        }

        return parent::handleRecordCreation($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('حفظ')
                ->action(fn() => $this->create())
                ->color('primary'),
            Action::make('cancel')
                ->label('إلغاء')
                ->url(ApplicationResource::getUrl('index'))
                ->color('gray')
                ->outlined(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return ApplicationResource::getUrl('index');
    }
}

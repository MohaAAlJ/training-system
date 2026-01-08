<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Trainee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
            $data['status'] = \App\Models\Application::STATUS_CONFIRMATION;
            $data['accepted_at'] = now();
        }

        if (empty($data['dob']) && isset($data['dob_year'])) {
            $data['dob'] = $data['dob_year'] . '-' . str_pad($data['dob_month'] ?? 1, 2, '0', STR_PAD_LEFT) . '-' . str_pad($data['dob_day'] ?? 1, 2, '0', STR_PAD_LEFT);
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
                    'college_id' => $collegeId,
                    'institution_id' => $institutionId,
                    'major_id' => $data['major_id'] ?? null,
                    'training_hours' => $data['training_hours'] ?? null,
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
                        'institution_id' => $data['institution_id'] ?? $trainee->institution_id,
                        'college_id' => $data['college_id'] ?? $trainee->college_id,
                        'major_id' => $data['major_id'] ?? $trainee->major_id,
                        'training_hours' => $data['training_hours'] ?? $trainee->training_hours,
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
            $data['dob'],
            $data['college_id'],
            $data['institution_id'],
            $data['major_id'],
            $data['training_hours'],
            $data['dob_year'],
            $data['dob_month'],
            $data['dob_day']
        );

        return $data;
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

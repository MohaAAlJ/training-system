<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationsResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Trainees;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CreateApplications extends CreateRecord
{
    protected static string $resource = ApplicationsResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تمت إضافة الطلب بنجاح';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (Auth::user()->isCollegeSupervisor()) {
            $data['status'] = \App\Helpers\Constans::STATUS_NEW;
        }

        if (empty($data['dob']) && isset($data['dob_year'])) {
            $data['dob'] = $data['dob_year'] . '-' . str_pad($data['dob_month'] ?? 1, 2, '0', STR_PAD_LEFT) . '-' . str_pad($data['dob_day'] ?? 1, 2, '0', STR_PAD_LEFT);
        }

        if (empty($data['trainee_id'])) {
            DB::beginTransaction();
            try {
                $traineeData = [
                    'full_name' => $data['full_name'] ?? 'New Trainee',
                    'national_id' => $data['national_id'] ?? null,
                    'phone_number' => $data['phone_number'] ?? null,
                    'governorate_id' => $data['governorate_id'] ?? null,
                    'address' => $data['address'] ?? null,
                    'street' => $data['street'] ?? null,
                    'dob' => $data['dob'] ?? null,
                    'college_id' => $data['college_id'] ?? null,
                    'institution_id' => $data['institution_id'] ?? null,
                    'major_id' => $data['major_id'] ?? null,
                    'training_hours' => $data['training_hours'] ?? null,
                ];

                $trainee = Trainees::create($traineeData);
                $data['trainee_id'] = $trainee->id;

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        }

        // Increment section capacity
        if (isset($data['section_id'])) {
            $section = \App\Models\Sections::find($data['section_id']);
            if ($section) {
                $section->increment('current_capacity');
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
                ->url(ApplicationsResource::getUrl('index'))
                ->color('gray')
                ->outlined(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return ApplicationsResource::getUrl('index');
    }
}

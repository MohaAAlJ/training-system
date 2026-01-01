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
        return 'تمت العملية بنجاح'; // or "Completed"
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Assemble dob from dob_day/dob_month/dob_year if provided
        if (empty($data['dob']) && (!empty($data['dob_year']) || !empty($data['dob_month']) || !empty($data['dob_day']))) {
            $year = $data['dob_year'] ?? null;
            $month = isset($data['dob_month']) ? str_pad($data['dob_month'], 2, '0', STR_PAD_LEFT) : '01';
            $day = isset($data['dob_day']) ? str_pad($data['dob_day'], 2, '0', STR_PAD_LEFT) : '01';
            if ($year) {
                $data['dob'] = "{$year}-{$month}-{$day}";
            }
        }
        // If the current user is a college user, enforce their college/institution
        $user = Auth::user();
        if ($user instanceof \App\Models\User && $user->isCollege()) {
            $userCollege = $user->college;
            if ($userCollege) {
                $data['college_id'] = $userCollege->id;
                $data['institution_id'] = $userCollege->institution_id ?? $userCollege->institution?->id ?? null;
            }
        }

        if (empty($data['trainee_id'])) {
            DB::beginTransaction();
            try {
                $trainee = Trainees::create([
                    'national_id' => $data['national_id'] ?? null,
                    'college_id' => $data['college_id'] ?? null,
                    'full_name' => $data['full_name'] ?? ('Trainee ' . now()->timestamp),
                    'phone_number' => $data['phone_number'] ?? null,
                    'dob' => $data['dob'] ?? null,
                    'address' => $data['address'] ?? null,
                    'institution_id' => $data['institution_id'] ?? null,
                    'major_id' => $data['major_id'] ?? null,
                ]);

                $data['trainee_id'] = $trainee->id;

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                report($e);
            }
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('حفظ')
                // ->icon('heroicon-o-check')
                ->action(function () {
                    $this->create();
                })
                ->color('primary'),
            Action::make('cancel')
                ->label('إلغاء')
                ->url(ApplicationsResource::getUrl('index'))
                ->color('danger')
                ->outlined(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return ApplicationsResource::getUrl('index');
    }
}

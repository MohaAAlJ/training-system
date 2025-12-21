<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationsResource;
use App\Models\Applications;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Trainees;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CreateApplications extends CreateRecord
{
    protected static string $resource = ApplicationsResource::class;

    public function mount(): void
    {
        $this->authorize('create', Applications::class);
        parent::mount();
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تمت إضافة الطلب بنجاح';
    }

    public function create(bool $another = false): void
    {
        DB::transaction(function () use ($another) {
            parent::create($another);
        });
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (Auth::user()->isCollegeSupervisor()) {
            $data['status'] = 'waiting';
        }

        if (empty($data['dob']) && isset($data['dob_year'])) {
            $data['dob'] = $data['dob_year'] . '-' . str_pad($data['dob_month'] ?? 1, 2, '0', STR_PAD_LEFT) . '-' . str_pad($data['dob_day'] ?? 1, 2, '0', STR_PAD_LEFT);
        }

        if (empty($data['trainee_id'])) {
            try {
                $traineeData = [
                    'full_name' => $data['full_name'] ?? 'New Trainee',
                    'national_id' => $data['national_id'] ?? null,
                    'phone_number' => $data['phone_number'] ?? null,
                    'address' => $data['address'] ?? null,
                    'dob' => $data['dob'] ?? null,
                    'college_id' => $data['college_id'] ?? null,
                    'institution_id' => $data['institution_id'] ?? null,
                    'major_id' => $data['major_id'] ?? null,
                ];

                $trainee = Trainees::create($traineeData);
                $data['trainee_id'] = $trainee->id;
            } catch (\Throwable $e) {
                throw $e;
            }
        }

        unset(
            $data['full_name'],
            $data['national_id'],
            $data['phone_number'],
            $data['address'],
            $data['dob'],
            $data['college_id'],
            $data['institution_id'],
            $data['major_id'],
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

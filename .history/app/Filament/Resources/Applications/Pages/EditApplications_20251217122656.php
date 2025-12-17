<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;

class EditApplications extends EditRecord
{
    protected static string $resource = ApplicationsResource::class;

    protected function getHeaderActions(): array
    {
        // ... (Keep your existing actions)
        return [
            Actions\Action::make('save')
                ->label('حفظ التعديلات')
                ->action(fn() => $this->save())
                ->color('primary'),
            Actions\Action::make('cancel')
                ->label('إلغاء')
                ->url(ApplicationsResource::getUrl('index'))
                ->color('gray')
                ->outlined(),
            Actions\DeleteAction::make()->visible(fn() => Auth::user()?->isAdmin()),
            // ...
        ];
    }

    // Keep this to fill the form when page loads
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        if ($record->trainee) {
            $t = $record->trainee;
            // Merge trainee data into the form data array
            $data = array_merge($data, [
                'trainee_id'   => $t->id,
                'national_id'  => $t->national_id,
                'full_name'    => $t->full_name,
                'phone_number' => $t->phone_number,
                'address'      => $t->address,
                'dob'          => $t->dob?->format('Y-m-d'), // Simplified date handling
                'institution_id' => $t->institution_id,
                'college_id'     => $t->college_id,
                'major_id'       => $t->major_id,
            ]);
        }

        return $data;
    }

    /**
     * BEST PRACTICE: Override the handle method.
     * This gives you full control over HOW the record is updated.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // 1. Logic for College Supervisor (Enforce College ID)
        $user = Auth::user();
        if ($user && $user->isCollegeSupervisor() && $user->college) {
            $data['college_id'] = $user->college->id;
            $data['institution_id'] = $user->college->institution_id;
        }

        // 2. Use a Transaction for data integrity
        DB::transaction(function () use ($record, $data) {

            // --- A. Handle Trainee Update ---
            if ($record->trainee) {
                // We define exactly which fields belong to Trainee
                $traineeData = Arr::only($data, [
                    'national_id',
                    'full_name',
                    'phone_number',
                    'dob',
                    'address',
                    'institution_id',
                    'college_id',
                    'major_id'
                ]);

                // Handle the split DOB logic if necessary (or rely on the datepicker returning Y-m-d)
                // If using separate day/month/year inputs:
                if (empty($traineeData['dob']) && !empty($data['dob_year'])) {
                    $year = $data['dob_year'];
                    $month = str_pad($data['dob_month'] ?? '01', 2, '0', STR_PAD_LEFT);
                    $day = str_pad($data['dob_day'] ?? '01', 2, '0', STR_PAD_LEFT);
                    $traineeData['dob'] = "{$year}-{$month}-{$day}";
                }

                $record->trainee->update($traineeData);
            }

            // --- B. Handle Application Update ---
            // Instead of manually unsetting, we take ONLY the fields that belong to the Application model.
            // This prevents "Column not found" errors automatically.

            // Get all columns in the applications table (or use $fillable)
            $applicationAttributes = $record->getFillable();

            // Filter $data to only keep keys that are in the application's fillable array
            $applicationData = Arr::only($data, $applicationAttributes);

            // Update the application record
            $record->update($applicationData);
        });

        return $record;
    }
}

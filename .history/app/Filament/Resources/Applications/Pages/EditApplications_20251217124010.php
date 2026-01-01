<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Trainees;

class EditApplications extends EditRecord
{
    protected static string $resource = ApplicationsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(fn() => Auth::user()?->isAdmin()),
            ForceDeleteAction::make()->visible(fn() => Auth::user()?->isAdmin()),
            RestoreAction::make()->visible(fn() => Auth::user()?->isAdmin()),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return ApplicationsResource::getUrl('index');
    }

    /**
     * Load trainee data into form when editing
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        if ($record && $record->trainee) {
            $trainee = $record->trainee;
            $data['trainee_id'] = $trainee->id;
            $data['national_id'] = $trainee->national_id;
            $data['full_name'] = $trainee->full_name;
            $data['phone_number'] = $trainee->phone_number;
            $data['dob'] = $trainee->dob;
            $data['address'] = $trainee->address;
            $data['institution_id'] = $trainee->institution_id;
            $data['college_id'] = $trainee->college_id;
            $data['major_id'] = $trainee->major_id;
        }

        return $data;
    }

    /**
     * Update trainee data when saving application
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = Auth::user();
        
        // Enforce college supervisor's college/institution
        if ($user instanceof \App\Models\User && $user->isCollegeSupervisor()) {
            $userCollege = $user->college;
            if ($userCollege) {
                $data['college_id'] = $userCollege->id;
                $data['institution_id'] = $userCollege->institution_id ?? $userCollege->institution?->id ?? null;
            }
        }

        // Update trainee if trainee_id exists
        if (!empty($data['trainee_id'])) {
            DB::beginTransaction();
            try {
                $trainee = Trainees::find($data['trainee_id']);
                if ($trainee) {
                    $trainee->update([
                        'national_id' => $data['national_id'] ?? $trainee->national_id,
                        'full_name' => $data['full_name'] ?? $trainee->full_name,
                        'phone_number' => $data['phone_number'] ?? $trainee->phone_number,
                        'dob' => $data['dob'] ?? $trainee->dob,
                        'address' => $data['address'] ?? $trainee->address,
                        'institution_id' => $data['institution_id'] ?? $trainee->institution_id,
                        'college_id' => $data['college_id'] ?? $trainee->college_id,
                        'major_id' => $data['major_id'] ?? $trainee->major_id,
                    ]);
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        }

        // Remove trainee-specific fields before updating application
        unset(
            $data['full_name'],
            $data['national_id'],
            $data['phone_number'],
            $data['dob'],
            $data['address'],
            $data['institution_id'],
            $data['college_id'],
            $data['major_id']
        );

        return $data;
    }
}

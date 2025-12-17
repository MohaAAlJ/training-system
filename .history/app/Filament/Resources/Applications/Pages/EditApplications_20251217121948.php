<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
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
            Action::make('save')
                ->label('حفظ التعديلات')
                ->action(function () {
                    $this->save();
                })
                ->color('primary'),
            Action::make('cancel')
                ->label('إلغاء')
                ->url(ApplicationsResource::getUrl('index'))
                ->color('gray')
                ->outlined(),

            DeleteAction::make()->visible(static fn() => Auth::user()?->isAdmin() ?? false)
                ->color('danger')
                ->outlined(),
            ForceDeleteAction::make()->visible(static fn() => Auth::user()?->isAdmin() ?? false)
                ->color('danger')
                ->outlined(),
            RestoreAction::make()->visible(static fn() => Auth::user()?->isAdmin() ?? false)
                ->color('danger')
                ->outlined(),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return ApplicationsResource::getUrl('index');
    }

    // تعبئة الفورم ببيانات المتدرب عند فتح الصفحة
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        // جلب بيانات المتدرب ووضعها في حقول الفورم
        if ($record && $record->trainee) {
            $t = $record->trainee;
            $data['trainee_id'] = $t->id; // مهم جداً
            $data['national_id'] = $t->national_id;
            $data['full_name'] = $t->full_name;
            $data['phone_number'] = $t->phone_number;

            // تقسيم تاريخ الميلاد
            if ($t->dob) {
                $data['dob_year'] = $t->dob->format('Y');
                $data['dob_month'] = $t->dob->format('n');
                $data['dob_day'] = $t->dob->format('j');
            }

            $data['address'] = $t->address;
            $data['institution_id'] = $t->institution_id;
            $data['college_id'] = $t->college_id;
            $data['major_id'] = $t->major_id;
        }

        return $data;
    }

    // معالجة البيانات قبل الحفظ
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // 1. ضمان أن مشرف الكلية لا يغير الكلية/المؤسسة
        $user = Auth::user();
        if ($user instanceof \App\Models\User && $user->isCollegeSupervisor()) {
            $userCollege = $user->college;
            if ($userCollege) {
                $data['college_id'] = $userCollege->id;
                $data['institution_id'] = $userCollege->institution_id ?? $userCollege->institution?->id ?? null;
            }
        }

        // 2. تحديث بيانات المتدرب (جدول منفصل)
        if (!empty($data['trainee_id'])) {
            DB::beginTransaction();
            try {
                $trainee = Trainees::find($data['trainee_id']);
                if ($trainee) {
                    // تجميع تاريخ الميلاد
                    if (empty($data['dob']) && (!empty($data['dob_year']) || !empty($data['dob_month']) || !empty($data['dob_day']))) {
                        $year = $data['dob_year'] ?? null;
                        $month = isset($data['dob_month']) ? str_pad($data['dob_month'], 2, '0', STR_PAD_LEFT) : '01';
                        $day = isset($data['dob_day']) ? str_pad($data['dob_day'], 2, '0', STR_PAD_LEFT) : '01';
                        if ($year) {
                            $data['dob'] = "{$year}-{$month}-{$day}";
                        }
                    }

                    // تحديث سجل المتدرب
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
                // يفضل إيقاف العملية أو تسجيل الخطأ
                // report($e);
            }
        }

        // 3. هام جداً: إزالة حقول المتدرب من المصفوفة قبل إرسالها لجدول Applications
        // لتجنب خطأ: Column not found
        unset(
            $data['full_name'],
            $data['national_id'],
            $data['phone_number'],
            $data['dob'],
            $data['dob_year'],
            $data['dob_month'],
            $data['dob_day'],
            $data['address'],
            $data['institution_id'],
            $data['college_id'],
            $data['major_id']
        );

        return $data;
    }
}

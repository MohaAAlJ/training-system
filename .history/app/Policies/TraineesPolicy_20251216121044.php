<?php

namespace App\Policies;

use App\Models\Trainees;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TraineesPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * 2. عرض تفاصيل متدرب (view)
     */
    public function view(User $user, Trainees $trainee): bool
    {
        // الأدمن والوزارة
        if ($user->isAdmin() || $user->isMinistry()) {
            return true;
        }

        // الكلية: تشوف طلابها فقط
        if ($user->isCollegeSupervisor()) {
            return $trainee->college_id === $user->college?->id;
        }

        // رئيس القسم: يشوف الطالب إذا كان مقدم طلب عنده في القسم
        if ($user->isDepartmentHead()) {
            return $trainee->applications()
                ->where('department_id', $user->department?->id)
                ->exists();
        }

        // المدير الإداري: يشوف الطالب إذا مقدم طلب في إدارة تابعة له
        if ($user->isAdministrative()) {
            return $trainee->applications()->whereHas('department', function ($q) use ($user) {
                $q->where('administrative_id', $user->administrative?->id);
            })->exists();
        }

        return false;
    }

    /**
     * 3. إنشاء متدرب (create)
     * حصرياً للكلية (عشان تسجل طلابها) والأدمن
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isCollegeSupervisor();
    }

    /**
     * 4. التعديل (update)
     * الكلية تعدل بيانات طلابها، والأدمن يعدل أي حدا
     */
    public function update(User $user, Trainees $trainee): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCollegeSupervisor()) {
            return $trainee->college_id === $user->college?->id;
        }

        return false;
    }

    /**
     * 5. الحذف (delete)
     * للأدمن فقط
     */
    public function delete(User $user, Trainees $trainee): bool
    {
        return $user->isAdmin();
    }
}

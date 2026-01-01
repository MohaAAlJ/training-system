<?php

namespace App\Policies;

use App\Models\Applications;
use App\Models\User;
use App\Helpers\Constans;

class ApplicationPolicy
{

    public function viewAny(User $user): bool
    {
        return true;
    }


    public function view(User $user, Applications $Applications): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCollegeSupervisor()) {
            return $Applications->trainee->college_id === $user->college?->id;
        }

        if ($user->isDepartmentHead()) {
            return $Applications->department_id === $user->department?->id;
        }

        if ($user->isAdministrative()) {
            return $Applications->department->administrative_id === $user->administrative?->id;
        }

        if ($user->isMinistry()) {
            return true; // أو ضع شرطاً خاصاً إذا كانت هناك أنواع محددة
        }

        return false;
    }

    /**
     * 3. إنشاء طلب (Create)
     * الكلية والوزارة والسوبر أدمن فقط
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isCollegeSupervisor() || $user->isMinistry();
    }

    /**
     * 4. التعديل (Update)
     * هنا نطبق قواعدك الصارمة: من يعدل الحالة؟
     */
    public function update(User $user, Applications $Applications): bool
    {
        // السوبر أدمن: يعدل كل شيء
        if ($user->isAdmin()) {
            return true;
        }

        // الإدارة: ممنوع التعديل نهائياً (عرض فقط)
        if ($user->isAdministrative()) {
            return false;
        }

        // رئيس القسم: مسموح التعديل (لتغيير الحالة) بشرط أن يكون الطلب في قسمه
        if ($user->isDepartmentHead()) {
            return $Applications->department_id === $user->department?->id;
        }

        // الكلية: مسموح التعديل *فقط* للموافقة المبدئية (تحويل من pending)
        if ($user->isCollegeSupervisor()) {
            return $Applications->trainee->college_id === $user->college?->id;
        }

        // الوزارة: مسموح للموافقة
        if ($user->isMinistry()) {
            return true;
        }

        return false;
    }

    // الحذف: للسوبر أدمن فقط
    public function delete(User $user, Applications $Applications): bool
    {
        return $user->isAdmin();
    }
}
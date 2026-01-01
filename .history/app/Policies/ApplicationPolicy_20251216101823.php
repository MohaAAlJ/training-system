<?php

namespace App\Policies;

use App\Models\Applications;
use App\Models\User;
use App\Helpers\Constans;

class ApplicationPolicy
{

    public function viewAny(User $user): bool
    {
        // الجميع مسموح لهم الدخول، لكن الفلترة ستتم في الكويري (سأوضحها لاحقاً)
        return true;
    }


    public function view(User $user, Applications $Applications): bool
    {
        // السوبر أدمن يرى كل شيء
        if ($user->isAdmin()) {
            return true;
        }

        // الكلية: ترى فقط طلبات طلاب كليتها
        if ($user->isCollegeSupervisor()) {
            // نفحص هل الطالب صاحب الطلب يتبع لكلية هذا المشرف
            return $Applications->trainee->college_id === $user->college?->id;
        }

        // رئيس القسم: يرى فقط الطلبات القادمة لقسمه
        if ($user->isDepartmentHead()) {
            return $Applications->department_id === $user->department?->id;
        }

        // الإدارة: ترى الطلبات الموجودة في الأقسام التابعة لها
        if ($user->isAdministrative()) {
            // هل القسم الذي فيه الطلب يتبع لهذه الإدارة؟
            return $Applications->department->administrative_id === $user->administrative?->id;
        }

        // الوزارة: ترى الطلبات (حسب منطقك الخاص بمزاولة المهنة)
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
<?php

namespace App\Policies;

use App\Models\Administratives;
use App\Models\User;

class AdministrativePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Administratives $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * 3. الإنشاء (create)
     * للأدمن فقط (لأنها هيكلية النظام).
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * 4. التعديل (update)
     * للأدمن فقط (تغيير اسم الإدارة، تعيين المدير، تغيير حالتها لطبية).
     */
    public function update(User $user, Administratives $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * 5. الحذف (delete)
     * للأدمن فقط.
     */
    public function delete(User $user, Administratives $model): bool
    {
        return $user->isAdmin();
    }
}

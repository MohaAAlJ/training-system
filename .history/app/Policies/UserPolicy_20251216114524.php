<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    // عرض التفاصيل
    public function view(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    // الإنشاء
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    // التعديل
    public function update(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    // الحذف
    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin();
    }
}
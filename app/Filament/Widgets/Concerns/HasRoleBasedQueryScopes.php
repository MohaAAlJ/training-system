<?php

namespace App\Filament\Widgets\Concerns;

use App\Models\Administrative;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shared role-based query scopes for Section-based widget queries.
 *
 * Use this trait in widgets whose base query is on the Section model.
 * Provides apply*Query helpers that narrow results to what each role may see.
 */
trait HasRoleBasedQueryScopes
{
    protected function applyMedicalManagerQuery(Builder $query, User $user): Builder
    {
        $adminUnit = Administrative::where('medical_head_user_id', $user->id)->active()->first();
        if (!$adminUnit) {
            return $query->whereRaw('0 = 1');
        }
        return $query
            ->where('administrative_id', $adminUnit->id)
            ->whereHas('departments', fn($q) => $q->where('is_medical', true)->active()->visible());
    }

    protected function applyHoaQuery(Builder $query, User $user): Builder
    {
        $adminUnit = $user->administrative()->active()->first();
        if (!$adminUnit) {
            return $query->whereRaw('0 = 1');
        }
        return $query->where('administrative_id', $adminUnit->id);
    }

    protected function applyDepartmentHeadQuery(Builder $query, User $user): Builder
    {
        if (!$user->department()->active()->exists()) {
            return $query->whereRaw('0 = 1');
        }
        return $query->whereHas('departments', fn($q) => $q->where('departments.id', $user->department->id)->visible());
    }

    protected function applySectionHeadQuery(Builder $query, User $user): Builder
    {
        if (!$user->section()->active()->exists()) {
            return $query->whereRaw('0 = 1');
        }
        return $query->where('id', $user->section->id);
    }

    protected function applyAssistantTrainingManagerSectionQuery(Builder $query, User $user): Builder
    {
        $managedDepartmentIds = $user->managedDepartmentIds();
        
        if (empty($managedDepartmentIds)) {
            return $query->whereRaw('0 = 1');
        }
        
        return $query->whereHas('departments', fn($q) => $q->whereIn('departments.id', $managedDepartmentIds)->visible());
    }

}

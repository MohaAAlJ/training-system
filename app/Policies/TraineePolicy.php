<?php

namespace App\Policies;

use App\Models\Trainee;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Models\Application;

class TraineePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Trainee $trainee): bool
    {
        if ($user->isAdmin() || $user->isMinistry() || $user->isGeneralTrainingManager() || $user->isMonitor()) {
            return true;
        }

        if ($user->isAssistantTrainingManager()) {
            return $trainee->applications()->forUser($user)->exists();
        }

        if ($user->isCollegeSupervisor()) {
            return $trainee->applications()->where('college_id', $user->college?->id)->exists();
        }

        if ($user->isSectionHead()) {
            return $trainee->applications()
                ->where('section_id', $user->section?->id)
                ->whereIn('status', [
                    Application::STATUS_STARTED_TRAINING,
                    Application::STATUS_ENDED_TRAINING
                ])
                ->exists();
        }

        if ($user->isAdministrative()) {
            return $trainee->applications()
                ->whereHas('section', fn($q) => $q->where('administrative_id', $user->administrative?->id))
                ->whereIn('status', [
                    Application::STATUS_STARTED_TRAINING,
                    Application::STATUS_ENDED_TRAINING
                ])
                ->exists();
        }

        if ($user->isDepartment()) {
            return $trainee->applications()
                ->whereHas('section', fn($q) => $q->whereHas('departments', fn($d) => $d->where('departments.id', $user->department?->id)->visible()))
                ->whereIn('status', [
                    Application::STATUS_STARTED_TRAINING,
                    Application::STATUS_ENDED_TRAINING
                ])
                ->exists();
        }

        if ($user->isMedicalManager()) {
            $adminId = \App\Models\Administrative::where('medical_head_user_id', $user->id)->value('id');
            return $trainee->applications()
                ->whereHas(
                    'section',
                    fn($q) =>
                    $q->where('administrative_id', $adminId)
                        ->whereHas('departments', fn($dq) => $dq->where('is_medical', true)->visible())
                )
                ->whereIn('status', [
                    Application::STATUS_STARTED_TRAINING,
                    Application::STATUS_ENDED_TRAINING
                ])
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Trainee $trainee): bool
    {
        if ($user->isAdmin() || $user->isGeneralTrainingManager()) {
            return true;
        }

        if ($user->isAssistantTrainingManager()) {
            return $trainee->applications()->forUser($user)->exists();
        }

        if ($user->isCollegeSupervisor()) {
            return $trainee->applications()->where('college_id', $user->college?->id)->exists();
        }

        if ($user->isMinistry()) {
            if ($user->mohDepartment) {
                if ($user->mohDepartment()->active()->doesntExist()) {
                    return false;
                }
                return $trainee->applications()
                    ->where('training_type', Application::PRACTICE)
                    ->whereHas('section', fn($sq) => $sq->whereHas('departments', fn($d) => $d->where('departments.id', $user->mohDepartment->id)->visible()))
                    ->exists();
            }
            return true;
        }

        return false;
    }

    public function delete(User $user, Trainee $trainee): bool
    {
        return $user->isAdmin();
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Application;

use App\Models\Application;
use App\Models\Administrative;
use App\Models\College;
use App\Models\Department;
use App\Models\Governorate;
use App\Models\Institution;
use App\Models\Major;
use App\Models\Section;
use App\Settings\TrainingSettings;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * FormDataLoaderService
 *
 * Handles loading and filtering form data for cascading selects.
 * Provides optimized queries with eager loading to prevent N+1 issues.
 *
 * Responsibilities:
 * - Load training types based on settings
 * - Load and cache governorates
 * - Load institutions and majors
 * - Load administrative units filtered by training type
 * - Load departments and sections with capacity information
 * - Filter dependent select options based on parent selections
 */
class FormDataLoaderService
{
    public function __construct(private TrainingSettings $settings) {}

    /**
     * Load available training types based on system settings
     *
     * @return Collection<int, array{id: int, name: string}>
     */
    public function loadTrainingTypes(): Collection
    {
        try {
            $types = [];

            if ($this->settings->enable_training_type_university) {
                $types[] = [
                    'id' => Application::UNIVERSITY,
                    'name' => Application::getTrainingTypeLongLabel(Application::UNIVERSITY),
                ];
            }

            if ($this->settings->enable_training_type_practice) {
                $types[] = [
                    'id' => Application::PRACTICE,
                    'name' => Application::getTrainingTypeLongLabel(Application::PRACTICE),
                ];
            }

            return collect($types);
        } catch (Exception $e) {
            Log::error('Failed to load training types', ['error' => $e->getMessage()]);

            return collect();
        }
    }

    /**
     * Load governorates for address selection
     *
     * @return Collection<int, array{id: int, name: string}>
     */
    public function loadGovernoratesIfNeeded(): Collection
    {
        try {
            $allGovs = Governorate::select('id', 'name')->get();
            $order = ['شمال غزة', 'غزة', 'محافظات الوسطى', 'خانيونس', 'رفح'];

            return $allGovs
                ->sortBy(function ($gov) use ($order) {
                    $key = array_search($gov->name, $order);
                    return $key === false ? 99 : $key;
                })
                ->map(fn($gov) => ['id' => $gov->id, 'name' => $gov->name])
                ->values();
        } catch (Exception $e) {
            Log::error('Failed to load governorates', ['error' => $e->getMessage()]);

            return collect();
        }
    }

    /**
     * Load active institutions
     *
     * @return Collection<int, array{id: int, name: string}>
     */
    public function loadInstitutions(): Collection
    {
        try {
            return Institution::active()
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
                ->map(fn($inst) => ['id' => $inst->id, 'name' => $inst->name])
                ->values();
        } catch (Exception $e) {
            Log::error('Failed to load institutions', ['error' => $e->getMessage()]);

            return collect();
        }
    }

    /**
     * Load administrative units (locations) based on training type.
     * Checks for medical filters and ensures sections are active.
     */
    public function loadAdministratives(?int $trainingType = null): Collection
    {
        try {
            $query = Administrative::query()->active();

            if ($trainingType === Application::PRACTICE) {
                // Ensure the administrative itself is marked medical
                $query->where('is_medical', true);

                // For Practice, ensure there is at least one active section
                // that belongs to an active medical department
                $query->whereHas('sections', function ($q) {
                    $q->active()->whereHas('departments', function ($q2) {
                        $q2->where('is_medical', true)->active()->visible();
                    });
                });
            } else {
                // Only show administratives that have at least one active section
                $query->whereHas('sections', function ($q) {
                    $q->active()->whereHas('departments', function ($q2) {
                        $q2->active()->visible();
                    });
                });
            }

            return $query
                ->with('governorate:id,name')
                ->select('id', 'name', 'address', 'governorate_id')
                ->orderBy('governorate_id')
                ->orderBy('id')
                ->get()
                ->map(fn($admin) => [
                    'id'               => $admin->id,
                    'name'             => $admin->name,
                    'address'          => $admin->address,
                    'governorate_id'   => $admin->governorate_id,
                    'governorate_name' => $admin->governorate?->name ?? '',
                ])
                ->values();
        } catch (Exception $e) {
            Log::error('Failed to load administratives', ['error' => $e->getMessage()]);

            return collect();
        }
    }

    /**
     * Load all majors with institution relationships
     *
     * @return Collection<int, array{id: int, name: string, collegeIds: array, institutionIds: array}>
     */
    public function loadAllMajors(): Collection
    {
        try {
            return Major::with(['colleges' => fn($q) => $q->where('colleges.active', true)->wherePivot('active', true)])
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
                ->map(fn($major) => [
                    'id' => $major->id,
                    'name' => $major->name,
                    'collegeIds' => $major->colleges->pluck('id')->toArray(),
                    'institutionIds' => $major->colleges->pluck('institution_id')->unique()->toArray(),
                ]);
        } catch (Exception $e) {
            Log::error('Failed to load majors', ['error' => $e->getMessage()]);

            return collect();
        }
    }

    /**
     * Load all departments filtered by training type
     *
     * @return Collection<int, array{id: int, name: string}>
     */
    public function loadAllDepartments(?int $trainingType = null): Collection
    {
        try {
            $query = Department::query()->active()->visible();

            // Filter by medical if practice training
            if ($trainingType === Application::PRACTICE) {
                $query->where('is_medical', true);
            }

            return $query
                ->select('id', 'name')
                ->orderBy('id')
                ->get()
                ->map(fn($dept) => [
                    'id' => $dept->id,
                    'name' => $dept->name,
                ]);
        } catch (Exception $e) {
            Log::error('Failed to load departments', ['error' => $e->getMessage()]);

            return collect();
        }
    }

    /**
     * Load all sections with capacity information using eager loading.
     * Optimized to prevent N+1 queries by loading application counts upfront.
     *
     * @return Collection<int, array{id: int, name: string, departmentId: int, administrativeId: int, isFull: bool}>
     */
    public function loadAllSections(?int $trainingType = null): Collection
    {
        try {
            // Eager load application count to prevent N+1 queries
            $sections = Section::with(['departments', 'administrative'])
                ->withCount([
                    'applications as active_applications_count' => fn($q) => $q->where('status', Application::STATUS_STARTED_TRAINING)
                ])
                ->active()
                ->whereHas('departments', fn($q) => $q->active()->visible())
                ->select('id', 'name', 'administrative_id', 'capacity')
                ->orderBy('id')
                ->get();

            // Filter by medical if practice training
            if ($trainingType === Application::PRACTICE) {
                $sections = $sections->filter(fn($sec) => $sec->departments->contains('is_medical', true));
            }

            return $sections->map(function (Section $section) {
                $total = (int) ($section->capacity ?? 0);
                $used = (int) $section->active_applications_count;
                $available = max(0, $total - $used);

                return [
                    'id' => $section->id,
                    'name' => $section->name,
                    'departmentIds' => $section->departments->pluck('id')->toArray(),
                    'administrativeId' => $section->administrative_id,
                    'isFull' => $total <= 0 || $available <= 0,
                ];
            })->values();
        } catch (Exception $e) {
            Log::error('Failed to load sections', ['error' => $e->getMessage()]);

            return collect();
        }
    }

    /**
     * Filter majors by institution
     *
     * @return Collection<int, array>
     */
    public function filterMajorsByInstitution(Collection $allMajors, int $institutionId): Collection
    {
        return $allMajors
            ->filter(fn($m) => in_array($institutionId, $m['institutionIds'] ?? []))
            ->values();
    }

    /**
     * Filter departments by administrative unit
     *
     * @return Collection<int, array>
     */
    public function filterDepartmentsByAdministrative(
        Collection $allDepartments,
        Collection $allSections,
        int $administrativeId
    ): Collection {
        $deptIds = $allSections
            ->filter(fn($s) => $s['administrativeId'] == $administrativeId)
            ->pluck('departmentIds')
            ->flatten()
            ->unique()
            ->toArray();

        return $allDepartments
            ->filter(fn($d) => in_array($d['id'], $deptIds))
            ->values();
    }

    /**
     * Filter sections by administrative and department
     *
     * @return Collection<int, array>
     */
    public function filterSectionsByDepartment(
        Collection $allSections,
        int $administrativeId,
        int $departmentId
    ): Collection {
        return $allSections
            ->filter(
                fn($s) =>
                $s['administrativeId'] == $administrativeId &&
                    in_array($departmentId, $s['departmentIds'])
            )
            ->values();
    }

    /**
     * Load college for a given major and institution
     */
    public function loadCollegeForMajor(int $majorId, int $institutionId): ?int
    {
        try {
            $college = College::whereHas('majors', function ($q) use ($majorId) {
                $q->where('majors.id', $majorId)
                    ->where('college_major.active', true);
            })
                ->where('institution_id', $institutionId)
                ->active()
                ->first();

            return $college?->id;
        } catch (Exception $e) {
            Log::error('Failed to load college', ['error' => $e->getMessage()]);

            return null;
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Application;

use App\Enums\TrainingType;
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
                    'id' => TrainingType::UNIVERSITY->value,
                    'name' => TrainingType::UNIVERSITY->label(),
                ];
            }

            if ($this->settings->enable_training_type_practice) {
                $types[] = [
                    'id' => TrainingType::PRACTICE->value,
                    'name' => TrainingType::PRACTICE->label(),
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
            return Governorate::select('id', 'name')
                ->orderBy('name')
                ->get()
                ->map(fn ($gov) => ['id' => $gov->id, 'name' => $gov->name]);
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
            return Institution::where('is_active', true)
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
                ->map(fn ($inst) => ['id' => $inst->id, 'name' => $inst->name]);
        } catch (Exception $e) {
            Log::error('Failed to load institutions', ['error' => $e->getMessage()]);

            return collect();
        }
    }

    /**
     * Load administrative units filtered by training type
     *
     * @return Collection<int, array{id: int, name: string}>
     */
    public function loadAdministratives(?int $trainingType = null): Collection
    {
        try {
            $query = Administrative::query();

            // Filter by medical if practice training
            if ($trainingType === TrainingType::PRACTICE->value) {
                $query->where('is_medical', true);
            }

            return $query
                ->select('id', 'title as name')
                ->orderBy('id')
                ->get()
                ->map(fn ($admin) => ['id' => $admin->id, 'name' => $admin->name]);
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
            return Major::with(['colleges'])
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
                ->map(fn ($major) => [
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
            $query = Department::query();

            // Filter by medical if practice training
            if ($trainingType === TrainingType::PRACTICE->value) {
                $query->where('is_medical', true);
            }

            return $query
                ->select('id', 'title as name')
                ->orderBy('id')
                ->get()
                ->map(fn ($dept) => [
                    'id' => $dept->id,
                    'name' => $dept->name,
                ]);
        } catch (Exception $e) {
            Log::error('Failed to load departments', ['error' => $e->getMessage()]);

            return collect();
        }
    }

    /**
     * Load all sections with capacity information
     *
     * @return Collection<int, array{id: int, name: string, departmentId: int, administrativeId: int, isFull: bool}>
     */
    public function loadAllSections(?int $trainingType = null): Collection
    {
        try {
            $sections = Section::with(['department', 'administrative'])
                ->select('id', 'name_location', 'department_id', 'administrative_id', 'capacity')
                ->orderBy('id')
                ->get();

            // Filter by medical if practice training
            if ($trainingType === TrainingType::PRACTICE->value) {
                $sections = $sections->filter(fn ($sec) => $sec->department?->is_medical);
            }

            return $sections->map(function ($section) {
                $stats = $section->getCapacityStats();

                return [
                    'id' => $section->id,
                    'name' => $section->name_location,
                    'departmentId' => $section->department_id,
                    'administrativeId' => $section->administrative_id,
                    'isFull' => $stats['is_full'] ?? false,
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
            ->filter(fn ($m) => in_array($institutionId, $m['institutionIds'] ?? []))
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
            ->filter(fn ($s) => $s['administrativeId'] == $administrativeId)
            ->pluck('departmentId')
            ->unique()
            ->toArray();

        return $allDepartments
            ->filter(fn ($d) => in_array($d['id'], $deptIds))
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
            ->filter(fn ($s) =>
                $s['administrativeId'] == $administrativeId &&
                $s['departmentId'] == $departmentId
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
                $q->where('major_id', $majorId);
            })
                ->where('institution_id', $institutionId)
                ->first();

            return $college?->id;
        } catch (Exception $e) {
            Log::error('Failed to load college', ['error' => $e->getMessage()]);

            return null;
        }
    }
}

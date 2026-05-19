<?php

namespace App\Livewire\Trainee\Concerns;

use App\Models\Governorate;
use App\Models\Institution;
use App\Models\Administrative;
use App\Models\Section;
use App\Models\Department;
use App\Models\College;
use App\Models\Major;
use App\Models\Application;
use App\Settings\TrainingSettings;
use Exception;

/**
 * Trait LoadsFormOptions
 *
 * Handles the loading of dropdown options for the TraineeForm component.
 * Implements "Load on Demand" strategy for performance optimization.
 *
 * Responsibilities:
 * - Loading initial static data (Training Types, Governorates, Institutions)
 * - Loading dependent data cascadingly (Majors, Departments, Sections)
 * - Filtering options based on user selection
 */
trait LoadsFormOptions
{

    // ========================================
    // DATABASE CALLS - Static & Initial Data
    // ========================================

    /**
     * Load available training types based on system settings.
     * Auto-selects the type if only one is available.
     *
     * @return void
     */
    private function loadTrainingTypes(): void
    {
        try {
            $loader = app(\App\Services\Application\FormDataLoaderService::class);
            $types = $loader->loadTrainingTypes();
            $this->trainingTypes = $types->toArray();

            if (count($this->trainingTypes) === 1) {
                $this->trainingType = (int) ($this->trainingTypes[0]['id'] ?? 0);
            }
        } catch (Exception $e) {
            $this->logException('Training Type Load Error', $e);
        }
    }

    /**
     * Load governorates list if not already loaded.
     * Sorts specifically by Gaza strip logical order.
     *
     * @return void
     */
    private function loadGovernoratesIfNeeded(): void
    {
        if (empty($this->governorates)) {
            try {
                $loader = app(\App\Services\Application\FormDataLoaderService::class);
                $this->governorates = $loader->loadGovernoratesIfNeeded()->toArray();
            } catch (Exception $e) {
                $this->logException('Failed to load governorates', $e);
            }
        }
    }

    /**
     * Load all active institutions.
     *
     * @return void
     */
    private function loadInstitutions(): void
    {
        try {
            $loader = app(\App\Services\Application\FormDataLoaderService::class);
            $this->institutions = $loader->loadInstitutions()->toArray();
        } catch (Exception $e) {
            $this->logException('Failed to load institutions', $e);
        }
    }

    /**
     * Load administrative units (locations) based on training type.
     * Filters for medical units if Practice training is selected.
     *
     * @return void
     */
    private function loadAdministratives(): void
    {
        try {
            $loader = app(\App\Services\Application\FormDataLoaderService::class);
            $this->administratives = $loader->loadAdministratives((int)$this->trainingType)->toArray();
        } catch (Exception $e) {
            $this->logException('Failed to load administratives', $e);
        }
    }

    // ========================================
    // CASCADING LOADERS - Dependent Data
    // ========================================

    /**
     * Load majors for the selected institution.
     *
     * @return void
     */
    private function loadMajors(): void
    {
        if (!$this->institutionId) {
            $this->majors = [];
            return;
        }

        try {
            $loader = app(\App\Services\Application\FormDataLoaderService::class);
            $allMajors = $loader->loadAllMajors();
            $this->majors = $loader->filterMajorsByInstitution($allMajors, (int)$this->institutionId)->toArray();
        } catch (Exception $e) {
            $this->logException('Failed to load majors', $e);
            $this->majors = [];
        }
    }

    /**
     * Load departments for the selected administrative unit.
     * Filters by medical status if Practice training.
     *
     * @return void
     */
    private function loadDepartments(): void
    {
        if (!$this->administrativeId) {
            $this->departments = [];
            return;
        }

        try {
            $loader = app(\App\Services\Application\FormDataLoaderService::class);
            $allDepartments = $loader->loadAllDepartments((int)$this->trainingType);
            $allSections = $loader->loadAllSections((int)$this->trainingType);

            $this->departments = $loader->filterDepartmentsByAdministrative(
                $allDepartments,
                $allSections,
                (int)$this->administrativeId
            )->toArray();
        } catch (Exception $e) {
            $this->logException('Failed to load departments', $e);
            $this->departments = [];
        }
    }

    /**
     * Load sections for selected department and administrative unit.
     * Includes capacity checks for UI feedback.
     *
     * @return void
     */
    private function loadSections(): void
    {
        if (!$this->departmentId || !$this->administrativeId) {
            $this->sections = [];
            return;
        }

        try {
            $loader = app(\App\Services\Application\FormDataLoaderService::class);
            $allSections = $loader->loadAllSections((int)$this->trainingType);

            $this->sections = $loader->filterSectionsByDepartment(
                $allSections,
                (int)$this->administrativeId,
                (int)$this->departmentId
            )->toArray();
        } catch (Exception $e) {
            $this->logException('Failed to load sections', $e);
            $this->sections = [];
        }
    }

    /**
     * Infer the College ID based on the selected Major and Institution.
     * Required for saving the application with correct hierarchy.
     *
     * @return void
     */
    private function loadCollegeId(): void
    {
        if (!$this->majorId || !$this->institutionId) {
            $this->collegeId = null;
            return;
        }

        try {
            $loader = app(\App\Services\Application\FormDataLoaderService::class);
            $this->collegeId = $loader->loadCollegeForMajor((int)$this->majorId, (int)$this->institutionId);
        } catch (Exception $e) {
            $this->logException('Failed to load college data', $e);
        }
    }
}

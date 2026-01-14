<?php

namespace App\Livewire\Trainee\Concerns;

/**
 * ManagesFormState Trait
 * 
 * Manages form state properties for TraineeForm component.
 * Separates state management from form logic for better code organization.
 * 
 * This trait handles:
 * - Form input properties (personal details, training details)
 * - Form visibility/conditional display
 * - Validation state
 * - Status messages
 */
trait ManagesFormState
{
    /**
     * Initialize form state properties
     */
    public function initializeFormState(): void
    {
        // Form inputs - Personal details
        $this->fullName = null;
        $this->nationalId = null;
        $this->phoneNumber = null;
        $this->dob = null;
        $this->governorateId = null;
        $this->street = null;

        // Form inputs - Training details
        $this->trainingType = null;
        $this->institutionId = null;
        $this->majorId = null;
        $this->administrativeId = null;
        $this->departmentId = null;
        $this->sectionId = null;
        $this->trainingHours = null;
        $this->collegeId = null;

        // Form controls
        $this->termsApproval = false;
        $this->letterFile = null;

        // Form state
        $this->showPersonalDetails = false;
        $this->showTrainingDetails = false;
        $this->isValidating = false;

        // Messages
        $this->statusMessage = '';
        $this->statusMessageType = 'note';
        $this->message = '';
        $this->messageType = 'info';

        // Restrictions
        $this->fullNameReadonly = false;
        $this->dobReadonly = false;
        $this->nationalIdReadonly = false;
        $this->trainingTypeReadonly = false;
    }

    /**
     * Reset form to initial state
     */
    public function resetForm(): void
    {
        $this->initializeFormState();
    }

    /**
     * Reset only personal details section
     */
    public function resetPersonalDetails(): void
    {
        $this->fullName = null;
        $this->phoneNumber = null;
        $this->governorateId = null;
        $this->street = null;
        $this->letterFile = null;
    }

    /**
     * Reset only training details section
     */
    public function resetTrainingDetails(): void
    {
        $this->institutionId = null;
        $this->majorId = null;
        $this->administrativeId = null;
        $this->departmentId = null;
        $this->sectionId = null;
        $this->trainingHours = null;
        $this->collegeId = null;
    }

    /**
     * Show/collapse personal details fieldset
     */
    public function togglePersonalDetails(bool $show = true): void
    {
        $this->showPersonalDetails = $show;
        if (!$show) {
            $this->resetPersonalDetails();
        }
    }

    /**
     * Show/collapse training details fieldset
     */
    public function toggleTrainingDetails(bool $show = true): void
    {
        $this->showTrainingDetails = $show;
        if (!$show) {
            $this->resetTrainingDetails();
        }
    }

    /**
     * Set status message
     */
    public function setStatusMessage(string $message, string $type = 'note'): void
    {
        $this->statusMessage = $message;
        $this->statusMessageType = $type;
    }

    /**
     * Clear status message
     */
    public function clearStatusMessage(): void
    {
        $this->statusMessage = '';
        $this->statusMessageType = 'note';
    }

    /**
     * Set toast message
     */
    public function showToast(string $message, string $type = 'info'): void
    {
        $this->message = $message;
        $this->messageType = $type;
        $this->dispatch('show-toast', message: $message, type: $type);
    }

    /**
     * Set field as readonly
     */
    public function setFieldReadonly(string $field, bool $readonly = true): void
    {
        if ($field === 'fullName') {
            $this->fullNameReadonly = $readonly;
        } elseif ($field === 'dob') {
            $this->dobReadonly = $readonly;
        } elseif ($field === 'nationalId') {
            $this->nationalIdReadonly = $readonly;
        }
    }

    /**
     * Check if form is valid and ready to submit
     */
    public function isFormValid(): bool
    {
        return $this->showPersonalDetails &&
            !empty($this->trainingType) &&
            !empty($this->nationalId) &&
            !empty($this->fullName) &&
            !empty($this->phoneNumber) &&
            !empty($this->governorateId) &&
            !empty($this->administrativeId) &&
            !empty($this->sectionId) &&
            $this->trainingHours > 0 &&
            $this->termsApproval;
    }

    /**
     * Get form completion percentage
     */
    public function getFormCompletionPercentage(): int
    {
        $totalFields = 14; // Total form fields
        $filledFields = 0;

        // Count filled fields
        if (!empty($this->trainingType)) $filledFields++;
        if (!empty($this->nationalId)) $filledFields++;
        if ($this->showPersonalDetails) {
            if (!empty($this->fullName)) $filledFields++;
            if (!empty($this->dob)) $filledFields++;
            if (!empty($this->phoneNumber)) $filledFields++;
            if (!empty($this->governorateId)) $filledFields++;
            if (!empty($this->street)) $filledFields++;
            if (!empty($this->administrativeId)) $filledFields++;
            if (!empty($this->departmentId)) $filledFields++;
            if (!empty($this->sectionId)) $filledFields++;
            if ($this->trainingHours > 0) $filledFields++;
            if ($this->termsApproval) $filledFields++;
        }

        return (int) (($filledFields / $totalFields) * 100);
    }
}

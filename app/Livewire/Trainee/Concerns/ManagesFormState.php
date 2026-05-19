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
        $this->existingApplicationId = null;

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
    }

    /**
     * Obsolete methods removed to favor computed properties or centralized reset logic in component
     */

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
}

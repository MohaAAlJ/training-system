<?php

namespace App\Livewire\Trainee;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Exception;

use App\Settings\TrainingSettings;
use App\Models\Application;
use App\Livewire\Trainee\Concerns\ManagesFormState;

/**
 * TraineeForm Livewire Component
 *
 * This component handles the complete trainee application form
 * with validation, cascading selects, file uploads, and existing application checks.
 *
 * Replaces vanilla JavaScript app.js with reactive Livewire state management.
 *
 * Uses:
 * - TraineeFormConfig: Centralized configuration and validation rules
 * - ManagesFormState: State management and form operations
 */
#[Layout('components.layouts.app')]
class TraineeForm extends Component
{
    use WithFileUploads;
    use ManagesFormState;
    use Concerns\LoadsFormOptions;

    // ========================================
    // FORM INPUTS - Public properties that bind to form
    // ========================================
    public ?string $fullName = null;
    public ?string $nationalId = null;
    public ?string $phoneNumber = null;
    public ?string $dob = null;
    public ?int $gender = null;
    public ?int $governorateId = null;
    public ?string $street = null;
    public ?int $institutionId = null;
    public ?int $majorId = null;
    public ?int $administrativeId = null;
    public ?int $departmentId = null;
    public ?int $sectionId = null;
    public ?int $trainingType = null;
    public ?int $trainingHours = null;
    public ?int $collegeId = null;
    public ?string $universityNumber = null;
    public bool $termsApproval = false;
    public $letterFile = null;
    public string $formUuid = '';

    // ========================================
    // STATE MANAGEMENT
    // ========================================
    public ?int $existingApplicationId = null;
    public bool $isValidating = false;
    public bool $isBlocked = false;
    public string $statusMessage = '';
    public string $statusMessageType = 'note';
    public array $applicationStatus = [];
    public string $message = '';
    public string $messageType = 'info';

    // ========================================
    // CACHED OPTIONS - Managed by LoadsFormOptions Trait
    // Arrays used instead of Collections for reliable Livewire hydration
    // ========================================
    public array $governorates = [];
    public array $institutions = [];
    public array $majors = [];
    public array $administratives = [];
    public array $departments = [];
    public array $sections = [];
    public array $trainingTypes = [];

    // ========================================
    // FORM RESTRICTIONS - For pre-filled data
    // ========================================
    public bool $fullNameReadonly = false;
    public bool $dobReadonly = false;
    public bool $nationalIdReadonly = false;
    public bool $isExistingTrainee = false;
    public array $originalTraineeData = [];

    // ========================================
    // FILE UPLOAD PREVIEW
    // ========================================
    public string $filePreviewType = '';
    public string $filePreviewUrl = '';

    // ========================================
    // INITIALIZATION & MOUNTING
    // ========================================

    /**
     * Component Mount Hook.
     * Initializes the form, loads initial data, and checks validation for pre-filled forms.
     *
     * @param string|null $nationalId Optional National ID for pre-filling
     * @param int|null $trainingType Optional Training Type for pre-filling
     * @return void|\Illuminate\Http\RedirectResponse
     */
    public function mount(?string $nationalId = null, ?int $trainingType = null)
    {
        // Check if public form is enabled
        $settings = app(TrainingSettings::class);
        if (!$settings->is_public_form_enabled) {
            return redirect()->route('training.welcome')->with('error', 'تقديم الطلبات عبر البوابة مغلق حالياً.');
        }

        // Initialize form state (from ManagesFormState trait)
        $this->initializeFormState();

        if ($nationalId) {
            $this->nationalId = $nationalId;
        }

        if ($trainingType) {
            $this->trainingType = $trainingType;
        }

        // Generate UUID for form security (prevents replay attacks)
        $this->formUuid = Str::uuid()->toString();

        $this->governorates = [];
        $this->institutions = [];
        $this->majors = [];
        $this->administratives = [];
        $this->departments = [];
        $this->sections = [];
        $this->trainingTypes = [];

        // Load initial data eagerly
        $this->loadTrainingTypes();
        $this->loadGovernoratesIfNeeded();
        $this->loadInstitutions();
        $this->loadAdministratives();

        // Check status if pre-filled
        if ($this->nationalId && $this->trainingType) {
            $this->checkApplicationStatus();
        }
    }

    // ========================================
    // LIFECYCLE HOOKS
    // ========================================

    /**
     * Component Hydrate Hook.
     * Runs on every request to ensure data state consistency.
     *
     * @return void
     */
    public function hydrate()
    {
        $this->ensureDataLoaded();
    }

    /**
     * Ensure dependent data dropdowns are populated based on current selections.
     * Critical for Livewire's stateless nature to persist dropdown options.
     *
     * @return void
     */
    public function ensureDataLoaded()
    {
        // Keep filtered lists in sync after hydration if selections exist
        if ($this->institutionId && empty($this->majors)) {
            $this->loadMajors();
        }
        if ($this->administrativeId && empty($this->departments)) {
            $this->loadDepartments();
        }
        if ($this->departmentId && empty($this->sections)) {
            $this->loadSections();
        }
    }

    /**
     * Livewire Updated Hook.
     * Validates fields in real-time and manages UI visibility.
     *
     * @return void
     */
    public function updated($property)
    {
        // Clear message when user interacts
        if ($property !== 'message' && $property !== 'messageType') {
            $this->clearMessage();
        }

        // Trigger status check if relevant data changed
        if (in_array($property, ['nationalId', 'dob'])) {
            $this->checkApplicationStatus();
        }
    }

    /**
     * Computed property for Personal Details section visibility.
     */
    #[\Livewire\Attributes\Computed]
    public function showPersonalDetails(): bool
    {
        if ($this->isBlocked) return false;

        if (empty($this->nationalId) || strlen($this->nationalId) !== 9) return false;
        if (empty($this->trainingType)) return false;
        if (empty($this->dob)) return false;

        if (!validatePalestinianId($this->nationalId)) return false;

        try {
            $age = \Carbon\Carbon::parse($this->dob)->age;
            $minAge = \App\Livewire\Trainee\Config\TraineeFormConfig::MIN_AGE;
            $maxAge = \App\Livewire\Trainee\Config\TraineeFormConfig::MAX_AGE;

            return $age >= $minAge && $age <= $maxAge;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Computed property for Training Details section visibility.
     */
    #[\Livewire\Attributes\Computed]
    public function showTrainingDetails(): bool
    {
        if (!$this->showPersonalDetails) return false;

        // Verify key personal fields are filled
        return !empty($this->fullName) &&
            !empty($this->gender) &&
            !empty($this->phoneNumber) &&
            !empty($this->governorateId) &&
            !empty($this->trainingType);
    }

    /**
     * Computed property for Terms visibility.
     */
    #[\Livewire\Attributes\Computed]
    public function showTerms(): bool
    {
        if (!$this->showTrainingDetails) return false;

        $filled = !empty($this->trainingType) &&
            !empty($this->administrativeId) &&
            !empty($this->departmentId) &&
            !empty($this->sectionId) &&
            !empty($this->trainingHours);

        if ($this->trainingType === Application::UNIVERSITY) {
            $filled = $filled && !empty($this->institutionId) && !empty($this->majorId);
        }

        return $filled;
    }

    /**
     * Validate Palestinian ID on blur (when user leaves the field)
     */
    #[\Livewire\Attributes\On('blur')]
    public function validatePalestinianIdOnBlur()
    {
        $idLength = strlen($this->nationalId ?? '');
        if ($idLength > 0 && $idLength !== 9) {
            $this->dispatchToast('رقم الهوية يجب أن يتكون من 9 أرقام.', 'error');
            return;
        }
        if ($idLength === 0) {
            return;
        }

        // Validate Palestinian National ID checksum
        if (!validatePalestinianId($this->nationalId)) {
            $this->dispatchToast('رقم الهوية الوطنية غير صحيح.', 'error');
            return;
        }

        // ID is valid, clear any previous error
        $this->clearMessage();
    }

    #[\Livewire\Attributes\On('update-dob')]
    #[\Livewire\Attributes\On('update-dob-alpine')]
    public function updateDob($dob)
    {
        $this->dob = $dob;
        $this->validateOnly('dob');
        $this->checkApplicationStatus();
    }



    public function updatedTrainingType(): void
    {
        $this->termsApproval = false;

        if ($this->trainingType) {
            $this->checkApplicationStatus();

            // Reload location dropdowns based on new training type
            $this->loadAdministratives();

            // Re-validate currently selected location data against the new list
            if ($this->administrativeId) {
                // Check if current administrative exists in new list
                $exists = collect($this->administratives)->contains('id', $this->administrativeId);
                if (!$exists) {
                    $this->administrativeId = null;
                    $this->departmentId = null;
                    $this->sectionId = null;
                    $this->departments = [];
                    $this->sections = [];
                } else {
                    $this->loadDepartments();
                    if ($this->departmentId) {
                        $deptExists = collect($this->departments)->contains('id', $this->departmentId);
                        if (!$deptExists) {
                            $this->departmentId = null;
                            $this->sectionId = null;
                            $this->sections = [];
                        } else {
                            $this->loadSections();
                            if ($this->sectionId) {
                                $sectionExists = collect($this->sections)->contains('id', $this->sectionId);
                                if (!$sectionExists) {
                                    $this->sectionId = null;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function updatedInstitutionId()
    {
        // Always reset dependent fields when institution changes
        $this->majorId = null;
        $this->collegeId = null;

        if ($this->institutionId) {
            $this->loadMajors();
        } else {
            $this->majors = [];
        }
    }

    public function updatedAdministrativeId()
    {
        if ($this->administrativeId) {
            $this->loadDepartments();
            $this->sections = []; // Reset sections
            $this->sectionId = null;
        } else {
            $this->departments = [];
            $this->sections = [];
            $this->departmentId = null;
            $this->sectionId = null;
        }
    }

    public function updatedDepartmentId()
    {
        if ($this->departmentId && $this->administrativeId) {
            $this->loadSections();
        } else {
            $this->sections = [];
            $this->sectionId = null;
        }
    }

    public function updatedMajorId()
    {
        if ($this->majorId) {
            $this->loadCollegeId();
        } else {
            $this->collegeId = null;
        }
    }

    public function updatedLetterFile()
    {
        // Handle file preview
        if ($this->letterFile) {
            $this->handleFilePreview();
        }
    }

    // ========================================
    // DATA LOADING - Delegated to LoadsFormOptions Trait
    // ========================================



    // ========================================
    // VALIDATION CHECKS
    // ========================================
    private function checkApplicationStatus(): void
    {
        if (strlen($this->nationalId ?? '') !== 9 || !$this->trainingType || !$this->dob) {
            return;
        }

        // Validate Palestinian National ID format and checksum
        if (!validatePalestinianId($this->nationalId)) {
            $this->setStatusMessage('رقم الهوية الوطنية غير صحيح.', 'error');
            $this->dispatchToast('رقم الهوية الوطنية غير صحيح.', 'error');
            return;
        }

        $this->isValidating = true;

        try {
            // Use the injected service to check eligibility
            $service = app(\App\Services\Application\ApplicationStatusService::class);
            $result = $service->checkApplicationEligibility(
                (string) $this->nationalId,
                (int) $this->trainingType,
                (string) $this->dob
            );

            // Handle Trainee DOB Mismatch specifically
            if (isset($result['message']) && $result['message'] === 'بيانات التحقق غير مطابقة للسجلات') {
                $this->isBlocked = true;
                $this->setStatusMessage('تاريخ الميلاد المدخل غير مطابق لرقم الهوية في سجلاتنا.', 'error');
                $this->dispatchToast('تاريخ الميلاد غير مطابق لرقم الهوية.', 'error');
                return;
            }

            // Process the result from the service
            $this->processApplicationStatusResult($result);

            // Prefill form if trainee data exists (even if blocked)
            if (isset($result['trainee_data'])) {
                $this->prefillForm($result['trainee_data']);
            }
        } catch (Exception $e) {
            Log::error('Application Status Check Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->logException('Application Status Check Error', $e);
        } finally {
            $this->isValidating = false;
        }
    }

    private function processApplicationStatusResult(array $result): void
    {
        if ($result['has_application'] ?? false) {
            // STATUS_NEW: allow editing the existing application
            if ($result['is_new_application'] ?? false) {
                $this->isBlocked = false;
                $this->existingApplicationId = $result['blocking_application']->id;
                $this->clearStatusMessage();
                $this->dispatchToast('لديك طلب قيد المعالجة بإمكانك تعديله', 'info');

                if (isset($result['application_data'])) {
                    $this->prefillApplicationData($result['application_data']);
                }
                return;
            }

            $this->isBlocked = true;
            $this->existingApplicationId = null;
            $statusText = $result['message'] ?? 'لا يمكنك تقديم طلب جديد في هذا الوقت';
            $this->setStatusMessage($statusText, 'error');
            $this->dispatchToast($statusText, 'error');
            $this->termsApproval = false;
        } else {
            $this->isBlocked = false;
            $this->existingApplicationId = null;
            $this->clearStatusMessage();
        }
    }

    private function prefillApplicationData(array $appData): void
    {
        // trainingType is intentionally NOT overwritten here — the user's current
        // selection must be preserved so they can change it freely.
        $this->sectionId       = isset($appData['section_id'])       ? (int) $appData['section_id']       : null;
        $this->administrativeId = isset($appData['administrative_id']) ? (int) $appData['administrative_id'] : null;
        $this->departmentId    = isset($appData['department_id'])    ? (int) $appData['department_id']    : null;
        $this->institutionId   = isset($appData['institution_id'])   ? (int) $appData['institution_id']   : null;
        $this->majorId         = isset($appData['major_id'])         ? (int) $appData['major_id']         : null;
        $this->collegeId       = isset($appData['college_id'])       ? (int) $appData['college_id']       : null;
        $this->universityNumber = $appData['university_number']      ?? null;
        $this->trainingHours   = isset($appData['training_hours'])   ? (int) $appData['training_hours']   : null;
        $this->street          = $appData['street']                  ?? $this->street;

        // Re-load cascading options based on prefilled values
        if ($this->administrativeId) {
            $this->loadDepartments();
        }
        if ($this->departmentId && $this->administrativeId) {
            $this->loadSections();
        }
        if ($this->institutionId) {
            $this->loadMajors();
        }
    }



    private function dispatchToast(string $message, string $type = 'success'): void
    {
        // Dispatch to browser in Livewire 3 format
        $this->dispatch('show-toast', type: $type, message: $message);
    }

    private function prefillForm(array $trainee): void
    {
        $this->isExistingTrainee = true;
        // Store trusted data to prevent tampering
        $this->originalTraineeData = $trainee;

        $this->dispatchToast('مرحباً بعودتك! تم استرجاع بياناتك السابقة', 'info');

        $this->fullName = $trainee['full_name'] ?? null;
        $this->fullNameReadonly = true;

        if (isset($trainee['dob'])) {
            $this->dob = \Carbon\Carbon::parse($trainee['dob'])->timezone(config('app.timezone'))->format('Y-m-d');
        } else {
            $this->dob = null;
        }
        $this->dobReadonly = true;

        $this->gender = $trainee['gender'] ?? null;

        $this->nationalIdReadonly = true;

        $this->phoneNumber = $trainee['phone_number'] ?? null;
        $this->governorateId = !empty($trainee['governorate_id']) ? (int) $trainee['governorate_id'] : null;
        $this->street = $trainee['street'] ?? null;

        // Education/Training Profile Data (Editable)
        $this->administrativeId = !empty($trainee['administrative_id']) ? (int) $trainee['administrative_id'] : $this->administrativeId;
        $this->departmentId = !empty($trainee['department_id']) ? (int) $trainee['department_id'] : $this->departmentId;
        $this->sectionId = !empty($trainee['section_id']) ? (int) $trainee['section_id'] : $this->sectionId;
        $this->institutionId = !empty($trainee['institution_id']) ? (int) $trainee['institution_id'] : null;
        $this->majorId = !empty($trainee['major_id']) ? (int) $trainee['major_id'] : null;
        $this->trainingHours = !empty($trainee['training_hours']) ? (int) $trainee['training_hours'] : null;

        // Load filtered lists if needed
        if ($this->institutionId) {
            $this->loadMajors();
        }
        if ($this->administrativeId) {
            $this->loadDepartments();
        }
        if ($this->departmentId) {
            $this->loadSections();
        }

        // Show personal details since we have data
        // Managed by computed property now
    }

    private function resetFormRestrictions(): void
    {
        $this->isExistingTrainee = false;
        $this->fullNameReadonly = false;
        $this->dobReadonly = false;
        $this->nationalIdReadonly = false;
    }

    // ========================================
    // FILE UPLOAD HANDLING
    // ========================================
    private function handleFilePreview(): void
    {
        if (!$this->letterFile) {
            $this->filePreviewType = '';
            $this->filePreviewUrl = '';
            return;
        }

        $mimeType = $this->letterFile->getMimeType();

        if (str_starts_with($mimeType, 'image/')) {
            $this->filePreviewType = 'image';
            $this->filePreviewUrl = $this->letterFile->temporaryUrl();
        } elseif ($mimeType === 'application/pdf') {
            $this->filePreviewType = 'pdf';
            $this->filePreviewUrl = $this->letterFile->temporaryUrl();
        }
    }

    // ========================================
    // UI INTERACTIONS
    // ========================================


    public function setMessage(string $text, string $type = 'note'): void
    {
        $this->message = $text;
        $this->messageType = $type;
        $toastType = match ($type) {
            'error' => 'error',
            'success' => 'success',
            'warning' => 'warning',
            default => 'info',
        };
        $this->dispatchToast($text, $toastType);
    }

    public function clearMessage(): void
    {
        $this->message = '';
        $this->messageType = 'note';
    }

    public function showError(string $message, Exception $exception = null): void
    {
        $this->setMessage($message, 'error');
        if ($exception) {
            Log::error($message, ['exception' => $exception]);
        }
    }

    // ========================================
    // VALIDATION METHODS
    // ========================================
    /**
     * Validate a single field on blur
     * Used in form inputs with wire:blur="validateField('fieldName')"
     */
    public function validateField(string $field): void
    {
        $this->validateOnly($field);
    }

    /**
     * Get validation rules.
     * Required for Livewire's validate() method.
     */
    public function rules(): array
    {
        return \App\Livewire\Trainee\Config\TraineeFormConfig::getValidationRules();
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return \App\Livewire\Trainee\Config\TraineeFormConfig::getValidationMessages();
    }

    #[\Livewire\Attributes\Computed]
    public function isFormReady(): bool
    {
        return $this->showTerms &&
            $this->termsApproval &&
            !$this->isValidating &&
            $this->getErrorBag()->isEmpty();
    }

    // ========================================
    // FORM SUBMISSION
    // ========================================

    /**
     * Handle form submission.
     * Validates input, checks eligibility, and delegates processing to TraineeSubmissionService.
     *
     * @param \App\Services\Trainee\TraineeSubmissionService $submissionService Injected service
     * @return void|\Illuminate\Http\RedirectResponse
     */
    public function submit(\App\Services\Trainee\TraineeSubmissionService $submissionService)
    {
        try {
            // When editing an existing STATUS_NEW application skip the eligibility check —
            // the record is already ours, blocking it here would prevent the update.
            if (!$this->existingApplicationId) {
                $service = app(\App\Services\Application\ApplicationStatusService::class);
                $statusResult = $service->checkApplicationEligibility(
                    (string) $this->nationalId,
                    (int) $this->trainingType,
                    (string) $this->dob
                );

                // If a STATUS_NEW application exists, capture its ID so we update it
                if ($statusResult['is_new_application'] ?? false) {
                    $this->existingApplicationId = $statusResult['blocking_application']->id;
                } elseif (!($statusResult['can_apply'] ?? true)) {
                    $this->showError($statusResult['message'] ?: 'لا يمكنك تقديم هذا الطلب حالياً.');
                    return;
                }
            }

            // Validate section belongs to selected administrative/department
            $section = \App\Models\Section::where('id', $this->sectionId)
                ->where('administrative_id', $this->administrativeId)
                ->whereHas('departments', fn($q) => $q->where('departments.id', $this->departmentId)->visible())
                ->first();

            if (!$section) {
                $this->showError('القسم المختار لا ينتمي للمكان والتخصص المحددين.');
                return;
            }

            // Prepare data for submission
            $data = [
                'fullName' => $this->fullName,
                'nationalId' => $this->nationalId,
                'phoneNumber' => $this->phoneNumber,
                'dob' => $this->dob,
                'gender' => $this->gender,
                'governorateId' => $this->governorateId,
                'street' => $this->street,
                'sectionId' => $this->sectionId,
                'universityNumber' => $this->universityNumber,
                'institutionId' => $this->institutionId,
                'collegeId' => $this->collegeId,
                'majorId' => $this->majorId,
                'trainingHours' => $this->trainingHours,
                'trainingType' => $this->trainingType,
            ];

            try {
                if ($this->existingApplicationId) {
                    $application = $submissionService->updateApplication(
                        $this->existingApplicationId,
                        $data,
                        $this->letterFile
                    );
                } else {
                    $application = $submissionService->submitApplication(
                        $data,
                        $this->letterFile,
                        $this->isExistingTrainee,
                        $this->originalTraineeData
                    );
                }

                if ($application) {
                    $this->setMessage('تم إرسال الطلب بنجاح. جاري التحويل...', 'success');
                    $this->resetForm();
                    return redirect()->route('training.welcome');
                }
            } catch (Exception $e) {
                // If the error message is user-friendly (e.g., section full), show it
                if ($e->getMessage() && !str_starts_with($e->getMessage(), 'SQLSTATE')) {
                    $this->showError($e->getMessage());
                } else {
                    $this->logException('Database Transaction Error', $e);
                    $this->showError('حدث خطأ أثناء حفظ الطلب. يرجى المحاولة مرة أخرى.');
                }
            }
        } catch (ValidationException $e) {
            $messages = [];
            foreach ($e->errors() as $field => $errors) {
                foreach ($errors as $error) {
                    $messages[] = $error;
                }
            }
            $this->showError(implode("\n", $messages));
        } catch (Exception $e) {
            $this->logException('Form Submission Error', $e);
            $this->showError('حدث خطأ غير متوقع: ' . $e->getMessage());
        }
    }


    public function resetForm(): void
    {
        $this->reset([
            'fullName',
            'nationalId',
            'phoneNumber',
            'dob',
            'gender',
            'governorateId',
            'street',
            'institutionId',
            'majorId',
            'administrativeId',
            'departmentId',
            'sectionId',
            'trainingType',
            'trainingHours',
            'collegeId',
            'termsApproval',
            'letterFile',
            'originalTraineeData',
            'existingApplicationId',
        ]);
        $this->resetFormRestrictions();
        $this->clearStatusMessage();
    }

    // ========================================
    // EXCEPTION LOGGING HELPER
    // ========================================
    private function logException(string $msg, Exception $e): void
    {
        Log::error($msg, [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }

    // ========================================
    // THEME TOGGLE
    // ========================================
    public function toggleTheme(): void
    {
        $this->dispatch('toggle-theme');
    }

    // ========================================
    // RENDERING
    // ========================================
    public function render()
    {
        return view('livewire.trainee.trainee-form', [
            'isUniversity' => $this->trainingType === Application::UNIVERSITY,
            'isLoading' => $this->isValidating,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Trainee;

use App\Actions\Application\SubmitApplicationAction;
use App\Enums\ApplicationStatus;
use App\Enums\TrainingType;
use App\Livewire\Trainee\Config\TraineeFormConfig;
use App\Livewire\Trainee\Concerns\ManagesFormState;
use App\Models\Trainee;
use App\Services\Application\ApplicationStatusService;
use App\Services\Application\FormDataLoaderService;
use App\Services\File\ApplicationLetterUploadService;
use App\Settings\TrainingSettings;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * TraineeForm Component
 *
 * Handles the complete trainee application form with:
 * - Multi-step form progression (training type → personal details → training details)
 * - Cascading selects (institution → major, administrative → department → section)
 * - File uploads with preview
 * - Application status checking with caching
 * - Form submission with transaction safety
 *
 * Delegates complex logic to services and actions:
 * - ApplicationStatusService: Status checking and eligibility
 * - FormDataLoaderService: Data loading and filtering
 * - ApplicationLetterUploadService: File handling
 * - SubmitApplicationAction: Form submission
 *
 * Uses Livewire v3 attributes for validation and security.
 */
#[Layout('components.layouts.app')]
class TraineeForm extends Component
{
    use WithFileUploads;
    use ManagesFormState;

    // ========================================
    // FORM INPUTS - Bound to view
    // ========================================
    #[Validate]
    public ?string $fullName = null;

    #[Validate]
    public ?string $nationalId = null;

    #[Validate]
    public ?string $phoneNumber = null;

    #[Validate]
    public ?string $dob = null;

    #[Validate]
    public ?int $governorateId = null;

    #[Validate]
    public ?string $street = null;

    #[Validate]
    public ?int $institutionId = null;

    #[Validate]
    public ?int $majorId = null;

    #[Validate]
    public ?int $administrativeId = null;

    #[Validate]
    public ?int $departmentId = null;

    #[Validate]
    public ?int $sectionId = null;

    #[Validate]
    public ?int $trainingType = null;

    #[Validate]
    public ?int $trainingHours = null;

    #[Validate]
    public ?int $collegeId = null;

    #[Validate]
    public bool $termsApproval = false;

    #[Validate]
    public $letterFile = null;

    // ========================================
    // STATE MANAGEMENT
    // ========================================
    public bool $showPersonalDetails = false;
    public bool $showTrainingDetails = false;
    public bool $isValidating = false;

    public string $statusMessage = '';
    public string $statusMessageType = 'note';

    public string $message = '';
    public string $messageType = 'info';

    // ========================================
    // FORM RESTRICTIONS
    // ========================================
    public bool $fullNameReadonly = false;
    public bool $dobReadonly = false;
    public bool $nationalIdReadonly = false;

    // ========================================
    // FILE UPLOAD PREVIEW
    // ========================================
    public string $filePreviewType = '';
    public string $filePreviewUrl = '';

    // ========================================
    // CACHED OPTIONS
    // ========================================
    public Collection $governorates;
    public Collection $institutions;
    public Collection $majors;
    public Collection $allMajors;
    public Collection $administratives;
    public Collection $allSections;
    public Collection $allDepartments;
    public Collection $departments;
    public Collection $sections;
    public Collection $trainingTypes;

    // ========================================
    // DEPENDENCIES (Injected)
    // ========================================
    private ?ApplicationStatusService $statusService = null;
    private ?FormDataLoaderService $dataLoaderService = null;
    private ?ApplicationLetterUploadService $fileUploadService = null;
    private ?SubmitApplicationAction $submitAction = null;
    private ?TrainingSettings $settings = null;

    #[Locked]
    public string $formUuid = '';

    // ========================================
    // LIFECYCLE HOOKS
    // ========================================

    /**
     * Initialize component
     */
    public function mount(?string $nationalId = null, ?int $trainingType = null): void
    {
        $this->initializeFormState();
        $this->formUuid = Str::uuid()->toString();

        // Initialize collections
        $this->initializeCollections();

        // Load initial data
        $this->loadInitialData();

        // Pre-fill if provided
        if ($nationalId) {
            $this->nationalId = $nationalId;
        }

        if ($trainingType) {
            $this->trainingType = $trainingType;
        }

        // Check status if pre-filled
        if ($this->nationalId && $this->trainingType && $this->dob) {
            $this->checkApplicationStatus();
        }
    }

    /**
     * Hydrate component state
     */
    public function hydrate(): void
    {
        $this->ensureDataLoaded();
    }

    /**
     * Handle property updates
     */
    public function updated(string $property): void
    {
        // Clear message when user interacts
        if (!in_array($property, ['message', 'messageType', 'statusMessage', 'statusMessageType'])) {
            $this->clearMessage();
        }

        // Handle conditional visibility
        if (in_array($property, ['nationalId', 'trainingType', 'dob'])) {
            $this->updateFormVisibility();

            // Check application status when all first fieldset fields are complete
            if ($this->isFirstFieldsetComplete()) {
                $this->checkApplicationStatus();
            }
        }
    }

    // ========================================
    // LIFECYCLE HELPER METHODS
    // ========================================

    /**
     * Initialize collections to prevent null errors
     */
    private function initializeCollections(): void
    {
        $this->governorates = collect();
        $this->institutions = collect();
        $this->majors = collect();
        $this->allMajors = collect();
        $this->administratives = collect();
        $this->allSections = collect();
        $this->allDepartments = collect();
        $this->departments = collect();
        $this->sections = collect();
        $this->trainingTypes = collect();
    }

    /**
     * Load initial form data
     */
    private function loadInitialData(): void
    {
        $loader = $this->getDataLoaderService();

        $this->trainingTypes = $loader->loadTrainingTypes();
        $this->governorates = $loader->loadGovernoratesIfNeeded();
        $this->institutions = $loader->loadInstitutions();
        $this->administratives = $loader->loadAdministratives();
        $this->allMajors = $loader->loadAllMajors();
        $this->allDepartments = $loader->loadAllDepartments();
        $this->allSections = $loader->loadAllSections();

        // Auto-select training type if only one available
        if ($this->trainingTypes->count() === 1) {
            $this->trainingType = $this->trainingTypes->first()['id'] ?? null;
        }
    }

    /**
     * Ensure data remains loaded after hydration
     */
    private function ensureDataLoaded(): void
    {
        if ($this->allDepartments->isEmpty()) {
            $this->loadInitialData();
        }

        // Keep filtered lists in sync
        if ($this->institutionId && $this->majors->isEmpty()) {
            $this->filterMajorsByInstitution();
        }

        if ($this->administrativeId && $this->departments->isEmpty()) {
            $this->filterDepartmentsByAdministrative();
        }

        if ($this->departmentId && $this->sections->isEmpty()) {
            $this->filterSectionsByDepartment();
        }
    }

    /**
     * Update form visibility based on validation state
     */
    private function updateFormVisibility(): void
    {
        $isFieldsetComplete = strlen($this->nationalId ?? '') === 9 &&
            $this->trainingType &&
            $this->dob;

        if (!$isFieldsetComplete) {
            $this->showPersonalDetails = false;
            $this->showTrainingDetails = false;
            $this->termsApproval = false;
        }
    }

    /**
     * Check if first fieldset is complete
     */
    private function isFirstFieldsetComplete(): bool
    {
        return strlen($this->nationalId ?? '') === 9 &&
            $this->trainingType !== null &&
            !empty($this->dob);
    }

    // ========================================
    // UPDATE HANDLERS - Cascade filtering
    // ========================================

    /**
     * Handle training type change
     */
    public function updatedTrainingType(): void
    {
        // Reset dependent fields
        $this->administrativeId = null;
        $this->departmentId = null;
        $this->sectionId = null;
        $this->institutionId = null;
        $this->majorId = null;
        $this->collegeId = null;
        $this->showPersonalDetails = false;
        $this->showTrainingDetails = false;
        $this->termsApproval = false;

        // Reload filtered data
        if ($this->trainingType) {
            $loader = $this->getDataLoaderService();
            $this->administratives = $loader->loadAdministratives($this->trainingType);
            $this->allDepartments = $loader->loadAllDepartments($this->trainingType);
            $this->allSections = $loader->loadAllSections($this->trainingType);
            $this->checkApplicationStatus();
        }
    }

    /**
     * Handle institution selection change
     */
    public function updatedInstitutionId(): void
    {
        if ($this->institutionId) {
            $this->filterMajorsByInstitution();
        } else {
            $this->majors = collect();
            $this->majorId = null;
        }
    }

    /**
     * Handle administrative selection change
     */
    public function updatedAdministrativeId(): void
    {
        $this->departmentId = null;
        $this->sectionId = null;

        if ($this->administrativeId) {
            $this->filterDepartmentsByAdministrative();
            $this->sections = collect();
        } else {
            $this->departments = collect();
            $this->sections = collect();
        }
    }

    /**
     * Handle department selection change
     */
    public function updatedDepartmentId(): void
    {
        $this->sectionId = null;

        if ($this->departmentId && $this->administrativeId) {
            $this->filterSectionsByDepartment();
        } else {
            $this->sections = collect();
        }
    }

    /**
     * Handle major selection change
     */
    public function updatedMajorId(): void
    {
        if ($this->majorId) {
            $this->loadCollegeForMajor();
        } else {
            $this->collegeId = null;
        }
    }

    /**
     * Handle file upload
     */
    public function updatedLetterFile(): void
    {
        if ($this->letterFile) {
            $this->handleFilePreview();
        }
    }

    // ========================================
    // FILTERING METHODS
    // ========================================

    /**
     * Filter majors by selected institution
     */
    private function filterMajorsByInstitution(): void
    {
        try {
            $loader = $this->getDataLoaderService();
            $this->majors = $loader->filterMajorsByInstitution(
                $this->allMajors,
                $this->institutionId ?? 0
            );
        } catch (Exception $e) {
            $this->logException('Failed to filter majors', $e);
            $this->majors = collect();
        }
    }

    /**
     * Filter departments by selected administrative unit
     */
    private function filterDepartmentsByAdministrative(): void
    {
        try {
            $loader = $this->getDataLoaderService();
            $this->departments = $loader->filterDepartmentsByAdministrative(
                $this->allDepartments,
                $this->allSections,
                $this->administrativeId ?? 0
            );
        } catch (Exception $e) {
            $this->logException('Failed to filter departments', $e);
            $this->departments = collect();
        }
    }

    /**
     * Filter sections by selected department
     */
    private function filterSectionsByDepartment(): void
    {
        try {
            $loader = $this->getDataLoaderService();
            $this->sections = $loader->filterSectionsByDepartment(
                $this->allSections,
                $this->administrativeId ?? 0,
                $this->departmentId ?? 0
            );
        } catch (Exception $e) {
            $this->logException('Failed to filter sections', $e);
            $this->sections = collect();
        }
    }

    /**
     * Load college for selected major
     */
    private function loadCollegeForMajor(): void
    {
        try {
            $loader = $this->getDataLoaderService();
            $this->collegeId = $loader->loadCollegeForMajor(
                $this->majorId ?? 0,
                $this->institutionId ?? 0
            );
        } catch (Exception $e) {
            $this->logException('Failed to load college', $e);
            $this->collegeId = null;
        }
    }

    // ========================================
    // APPLICATION STATUS CHECKING
    // ========================================

    /**
     * Check application eligibility for trainee
     */
    private function checkApplicationStatus(): void
    {
        if (!$this->isFirstFieldsetComplete()) {
            return;
        }

        // Validate Palestinian ID
        if (validatePalestinianId($this->nationalId) !== 'valid') {
            $this->setStatusMessage('رقم الهوية الوطنية غير صحيح.', 'error');
            $this->dispatchToast('رقم الهوية الوطنية غير صحيح.', 'error');
            $this->showPersonalDetails = false;
            $this->showTrainingDetails = false;

            return;
        }

        $this->isValidating = true;

        try {
            $service = $this->getStatusService();
            $result = $service->checkApplicationEligibility(
                $this->nationalId,
                $this->trainingType,
                $this->dob
            );

            if ($result['can_apply']) {
                $this->clearStatusMessage();
                $this->showPersonalDetails = true;
                $this->showTrainingDetails = true;

                // Prefill with existing trainee data
                if ($traineeData = $service->getTraineeDataForPrefill($this->nationalId)) {
                    $this->prefillForm($traineeData);
                }
            } else {
                $this->setStatusMessage($result['message'], 'error');
                $this->dispatchToast($result['message'], 'error');
                $this->showPersonalDetails = false;
                $this->showTrainingDetails = false;
                $this->termsApproval = false;
            }
        } catch (Exception $e) {
            Log::error('Application status check error', [
                'message' => $e->getMessage(),
                'nationalId' => $this->nationalId,
            ]);
            $this->logException('Application Status Check Error', $e);
        } finally {
            $this->isValidating = false;
        }
    }

    // ========================================
    // FILE HANDLING
    // ========================================

    /**
     * Generate file preview information
     */
    private function handleFilePreview(): void
    {
        if (!$this->letterFile) {
            $this->filePreviewType = '';
            $this->filePreviewUrl = '';

            return;
        }

        try {
            $preview = $this->getFileUploadService()->getFilePreview($this->letterFile);
            $this->filePreviewType = $preview['type'];
            $this->filePreviewUrl = $preview['url'];
        } catch (Exception $e) {
            Log::error('Failed to generate file preview', ['error' => $e->getMessage()]);
            $this->filePreviewType = '';
            $this->filePreviewUrl = '';
        }
    }

    // ========================================
    // FORM SUBMISSION
    // ========================================

    /**
     * Submit application form
     */
    public function submit()
    {
        try {
            // Validate all fields
            $validated = $this->validate();

            // Double-check eligibility
            $service = $this->getStatusService();
            $eligibility = $service->checkApplicationEligibility(
                $validated['nationalId'],
                $validated['trainingType'],
                $validated['dob']
            );

            if (!$eligibility['can_apply']) {
                $this->setMessage($eligibility['message'], 'error');

                return;
            }

            // Submit application via action
            $action = $this->getSubmitAction();
            $application = $action->execute(
                $validated,
                $this->letterFile
            );

            $this->setMessage('تم إرسال الطلب بنجاح. جاري التحويل...', 'success');
            $this->reset();

            return redirect()->route('training.welcome');
        } catch (ValidationException $e) {
            $this->handleValidationError($e);
        } catch (Exception $e) {
            $this->logException('Form submission error', $e);
            $this->setMessage('حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى.', 'error');
        }
    }

    /**
     * Handle validation errors
     */
    private function handleValidationError(ValidationException $e): void
    {
        $messages = [];

        foreach ($e->errors() as $errors) {
            foreach ($errors as $error) {
                $messages[] = $error;
            }
        }

        $errorMessage = implode("\n", $messages);
        $this->setMessage($errorMessage, 'error');
    }

    // ========================================
    // FORM PREFILLING
    // ========================================

    /**
     * Prefill form with existing trainee data
     */
    private function prefillForm(array $trainee): void
    {
        $this->fullName = $trainee['full_name'] ?? null;
        $this->fullNameReadonly = true;

        if (isset($trainee['dob'])) {
            $this->dob = is_string($trainee['dob'])
                ? \Carbon\Carbon::parse($trainee['dob'])->format('Y-m-d')
                : $trainee['dob'];
        }
        $this->dobReadonly = true;
        $this->nationalIdReadonly = true;

        $this->phoneNumber = $trainee['phone_number'] ?? null;
        $this->governorateId = isset($trainee['governorate_id']) && $trainee['governorate_id']
            ? (int) $trainee['governorate_id']
            : null;
        $this->street = $trainee['street'] ?? null;
        $this->institutionId = isset($trainee['institution_id']) && $trainee['institution_id']
            ? (int) $trainee['institution_id']
            : null;
        $this->majorId = isset($trainee['major_id']) && $trainee['major_id']
            ? (int) $trainee['major_id']
            : null;
        $this->trainingHours = isset($trainee['training_hours']) && $trainee['training_hours']
            ? (int) $trainee['training_hours']
            : null;

        // Load filtered lists if needed
        if ($this->institutionId) {
            $this->filterMajorsByInstitution();
        }

        $this->showPersonalDetails = true;
        $this->showTrainingDetails = true;
    }

    // ========================================
    // MESSAGE MANAGEMENT
    // ========================================

    /**
     * Set status message (application eligibility related)
     */
    private function setStatusMessage(string $text, string $type = 'note'): void
    {
        $this->statusMessage = $text;
        $this->statusMessageType = $type;
    }

    /**
     * Clear status message
     */
    private function clearStatusMessage(): void
    {
        $this->statusMessage = '';
        $this->statusMessageType = 'note';
    }

    /**
     * Set general message (form interaction related)
     */
    private function setMessage(string $text, string $type = 'note'): void
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

    /**
     * Clear general message
     */
    public function clearMessage(): void
    {
        $this->message = '';
        $this->messageType = 'info';
    }

    /**
     * Dispatch toast notification to browser
     */
    private function dispatchToast(string $message, string $type = 'success'): void
    {
        $this->dispatch('show-toast', type: $type, message: $message);
    }

    // ========================================
    // VALIDATION RULES
    // ========================================

    /**
     * Get validation rules based on form state
     */
    protected function rules(): array
    {
        $config = TraineeFormConfig::class;

        $rules = [
            'trainingType' => 'required|in:' . implode(',', TrainingType::values()),
            'nationalId' => [
                'required',
                'digits:9',
                'regex:' . $config::NATIONAL_ID_REGEX,
                function ($attribute, $value, $fail) {
                    if (validatePalestinianId($value) !== 'valid') {
                        $fail('رقم الهوية الوطنية غير صحيح.');
                    }
                },
            ],
            'dob' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:' . now()->subYears($config::MIN_AGE)->format('Y-m-d'),
                'after_or_equal:' . now()->subYears($config::MAX_AGE)->format('Y-m-d'),
            ],
        ];

        if ($this->showPersonalDetails) {
            $rules = array_merge($rules, [
                'fullName' => 'required|string|regex:' . $config::NAME_REGEX . '|max:150',
                'phoneNumber' => [
                    'required',
                    'string',
                    'regex:' . $config::PHONE_REGEX,
                ],
                'governorateId' => 'required|exists:governorates,id',
                'street' => 'required|string|max:255',
                'trainingHours' => 'required|integer|min:' . $config::MIN_TRAINING_HOURS . '|max:' . $config::MAX_TRAINING_HOURS,
                'administrativeId' => 'required|exists:administratives,id',
                'departmentId' => 'required|exists:departments,id',
                'sectionId' => 'required|exists:sections,id',
            ]);

            if ($this->trainingType === TrainingType::UNIVERSITY->value) {
                $rules = array_merge($rules, [
                    'institutionId' => 'required|exists:institutions,id',
                    'majorId' => 'required|exists:majors,id',
                ]);
            }
        }

        if ($this->showTrainingDetails) {
            $rules['termsApproval'] = 'required|accepted';
        }

        return $rules;
    }

    // ========================================
    // SERVICE INJECTION
    // ========================================

    /**
     * Get or resolve ApplicationStatusService
     */
    private function getStatusService(): ApplicationStatusService
    {
        return $this->statusService ??= app(ApplicationStatusService::class);
    }

    /**
     * Get or resolve FormDataLoaderService
     */
    private function getDataLoaderService(): FormDataLoaderService
    {
        return $this->dataLoaderService ??= app(FormDataLoaderService::class);
    }

    /**
     * Get or resolve ApplicationLetterUploadService
     */
    private function getFileUploadService(): ApplicationLetterUploadService
    {
        return $this->fileUploadService ??= app(ApplicationLetterUploadService::class);
    }

    /**
     * Get or resolve SubmitApplicationAction
     */
    private function getSubmitAction(): SubmitApplicationAction
    {
        return $this->submitAction ??= app(SubmitApplicationAction::class);
    }

    // ========================================
    // HELPERS
    // ========================================

    /**
     * Log exception details
     */
    private function logException(string $message, Exception $e): void
    {
        Log::error($message, [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }

    /**
     * Toggle theme
     */
    public function toggleTheme(): void
    {
        $this->dispatch('toggle-theme');
    }

    // ========================================
    // RENDERING
    // ========================================

    /**
     * Render component view
     */
    public function render()
    {
        return view('livewire.trainee.trainee-form', [
            'isUniversity' => $this->trainingType === TrainingType::UNIVERSITY->value,
            'isLoading' => $this->isValidating,
        ]);
    }
}

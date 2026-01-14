<?php

namespace App\Livewire\Trainee;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Models\Governorate;
use App\Models\Institution;
use App\Models\Administrative;
use App\Models\Section;
use App\Models\Department;
use App\Models\College;
use App\Settings\TrainingSettings;
use App\Models\Trainee;
use App\Models\Application;
use App\Models\Major;
use App\Livewire\Trainee\Config\TraineeFormConfig;
use App\Livewire\Trainee\Concerns\ManagesFormState;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Exception;

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

    // ========================================
    // FORM INPUTS - Public properties that bind to form
    // ========================================
    public ?string $fullName = null;
    public ?string $nationalId = null;
    public ?string $phoneNumber = null;
    public ?string $dob = null;
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
    public bool $termsApproval = false;
    public $letterFile = null;
    public string $formUuid = '';

    // ========================================
    // STATE MANAGEMENT
    // ========================================
    public bool $showPersonalDetails = false;  // Show 2nd fieldset only after 1st fieldset is valid
    public bool $showTrainingDetails = false;  // Show 3rd fieldset only after 2nd fieldset is valid
    public bool $isValidating = false;
    public string $statusMessage = '';  // Application status check message
    public string $statusMessageType = 'note'; // note, error, warning, success
    public array $applicationStatus = []; // Status from check-existing-application endpoint
    public string $message = ''; // Toast message
    public string $messageType = 'info'; // Toast message type

    // ========================================
    // CACHED OPTIONS - Prevent unnecessary API calls
    // ========================================
    public Collection $governorates;
    public Collection $institutions;
    public Collection $majors;
    public Collection $allMajors;  // Load ALL majors once for client-side filtering
    public Collection $administratives;
    public Collection $allSections;  // Load ALL sections once for client-side filtering
    public Collection $allDepartments;  // Load ALL departments once for client-side filtering
    public Collection $departments;
    public Collection $sections;
    public Collection $trainingTypes;

    // ========================================
    // FORM RESTRICTIONS - For pre-filled data
    // ========================================
    public bool $fullNameReadonly = false;
    public bool $dobReadonly = false;
    public bool $nationalIdReadonly = false;

    // ========================================
    // FILE UPLOAD PREVIEW
    // ========================================
    public string $filePreviewType = ''; // 'image' or 'pdf'
    public string $filePreviewUrl = '';

    // ========================================
    // VALIDATION RULES & MESSAGES
    // ========================================
    protected function rules()
    {
        $config = TraineeFormConfig::class;

        $rules = [
            'trainingType' => 'required|in:' . Application::TRAINING_TYPE_UNIVERSITY . ',' . Application::TRAINING_TYPE_PRACTICE,
            'nationalId' => 'required|digits:9|regex:' . $config::NATIONAL_ID_REGEX,
        ];

        if ($this->showPersonalDetails) {
            $rules = array_merge($rules, [
                'fullName' => 'required|string|regex:' . $config::NAME_REGEX . '|max:150',
                'dob' => [
                    'required',
                    'date_format:Y-m-d',
                    'before_or_equal:' . now()->subYears($config::MIN_AGE)->format('Y-m-d'),
                    'after_or_equal:' . now()->subYears($config::MAX_AGE)->format('Y-m-d'),
                ],
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

            if ($this->trainingType === Application::TRAINING_TYPE_UNIVERSITY) {
                $rules = array_merge($rules, [
                    'institutionId' => 'required|exists:institutions,id',
                    'majorId' => 'required|exists:majors,id',
                ]);
            }
        }

        if ($this->showTrainingDetails) {
            $rules = array_merge($rules, [
                'termsApproval' => 'required|accepted',
            ]);
        }

        return $rules;
    }

    // ========================================
    // INITIALIZATION & MOUNTING
    // ========================================
    public function mount()
    {
        // Initialize form state (from ManagesFormState trait)
        $this->initializeFormState();

        // Generate UUID for form security (prevents replay attacks)
        $this->formUuid = Str::uuid()->toString();

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

        // Load initial data eagerly
        $this->loadTrainingTypes();
        $this->loadGovernoratesIfNeeded();
        $this->loadInstitutions();
        $this->loadAdministratives();
        $this->loadAllMajors();  // Load all majors for client-side filtering
        $this->loadAllDepartments();  // Load all departments for client-side filtering
        $this->loadAllSections();  // Load all sections for client-side filtering
    }

    // ========================================
    // LIFECYCLE HOOKS
    // ========================================
    #[\Livewire\Attributes\Hydrate]
    public function ensureDataLoaded()
    {
        // Ensure dropdown data is always loaded and available
        if ($this->allDepartments->isEmpty()) {
            $this->loadAllDepartments();
        }
        if ($this->allSections->isEmpty()) {
            $this->loadAllSections();
        }
        if ($this->allMajors->isEmpty()) {
            $this->loadAllMajors();
        }

        // Keep filtered lists in sync after hydration
        if ($this->institutionId && $this->majors->isEmpty()) {
            $this->loadFilteredMajors();
        }
        if ($this->administrativeId && $this->departments->isEmpty()) {
            $this->loadFilteredDepartments();
        }
        if ($this->departmentId && $this->sections->isEmpty()) {
            $this->loadFilteredSections();
        }
    }

    public function updated($property)
    {
        // Clear message when user interacts
        if ($property !== 'message' && $property !== 'messageType') {
            $this->clearMessage();
        }

        // Hide form if requirements are not met (less than 9 digits or no type selected)
        if ($property === 'nationalId' || $property === 'trainingType') {
            if (strlen($this->nationalId ?? '') < 9 || empty($this->trainingType)) {
                $this->showPersonalDetails = false;
                $this->showTrainingDetails = false;
                $this->termsApproval = false;
            }

            // Both first fieldset fields are complete - check application status
            if (strlen($this->nationalId ?? '') === 9 && $this->trainingType) {
                $this->checkApplicationStatus();
            }
        }
    }

    #[\Livewire\Attributes\On('update-dob')]
    public function updateDob($dob)
    {
        $this->dob = $dob;
        $this->validateOnly('dob');
    }

    #[\Livewire\Attributes\On('update-dob-alpine')]
    public function updateDobAlpine($dob)
    {
        $this->dob = $dob;
        $this->validateOnly('dob');
    }

    public function updatedNationalId()
    {
        // National ID update is now handled in the updated() method
        // This ensures it works whether nationalId or trainingType is updated last
    }

    public function updatedTrainingType()
    {
        // Reset dependent fields when training type changes
        $this->administrativeId = null;
        $this->departmentId = null;
        $this->sectionId = null;
        $this->institutionId = null;
        $this->majorId = null;
        $this->collegeId = null;
        $this->showPersonalDetails = false;
        $this->showTrainingDetails = false;
        $this->termsApproval = false;

        // Reload all data filtered by training type when training type is selected
        if ($this->trainingType) {
            $this->loadAdministratives();  // Filters by medical if practice training
            $this->loadAllDepartments();    // Filters by medical if practice training
            $this->loadAllSections();       // Reloads sections with updated capacity
            $this->checkApplicationStatus();
        }
    }

    public function updatedInstitutionId()
    {
        // Load majors when institution changes
        if ($this->institutionId) {
            $this->loadFilteredMajors();
        } else {
            $this->majors = collect();
            $this->majorId = null;
        }
    }

    private function loadFilteredMajors(): void
    {
        try {
            $this->majors = $this->allMajors
                ->filter(fn($m) => in_array($this->institutionId, $m['institutionIds'] ?? []))
                ->values();
        } catch (Exception $e) {
            $this->logException('Failed to filter majors', $e);
        }
    }

    public function updatedAdministrativeId()
    {
        // Reset dependent fields and reload filtered lists
        $this->departmentId = null;
        $this->sectionId = null;
        
        if ($this->administrativeId) {
            $this->loadFilteredDepartments();
            $this->sections = collect();
        } else {
            $this->departments = collect();
            $this->sections = collect();
        }
    }

    public function updatedDepartmentId()
    {
        // Reset section and reload filtered sections
        $this->sectionId = null;
        
        if ($this->departmentId && $this->administrativeId) {
            $this->loadFilteredSections();
        } else {
            $this->sections = collect();
        }
    }

    private function loadFilteredDepartments(): void
    {
        try {
            // Get department IDs that have sections in this administrative location
            $deptIds = $this->allSections
                ->filter(fn($s) => $s['administrativeId'] == $this->administrativeId)
                ->pluck('departmentId')
                ->unique()
                ->toArray();

            $this->departments = $this->allDepartments
                ->filter(fn($d) => in_array($d['id'], $deptIds))
                ->values();
        } catch (Exception $e) {
            $this->logException('Failed to filter departments', $e);
        }
    }

    private function loadFilteredSections(): void
    {
        try {
            $this->sections = $this->allSections
                ->filter(fn($s) => 
                    $s['administrativeId'] == $this->administrativeId && 
                    $s['departmentId'] == $this->departmentId
                )
                ->values();
        } catch (Exception $e) {
            $this->logException('Failed to filter sections', $e);
        }
    }

    public function updatedMajorId()
    {
        // Load college ID when major changes
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
    // DATE PICKER METHODS
    // ========================================
    // DATABASE CALLS - Load options directly from database (no HTTP)
    // ========================================
    private function loadTrainingTypes(): void
    {
        try {
            // Load training types from settings based on enabled types
            $settings = app(TrainingSettings::class);
            $types = [];

            if ($settings->enable_training_type_university) {
                $types[] = ['id' => Application::TRAINING_TYPE_UNIVERSITY, 'name' => Application::TRAINING_TYPES[Application::TRAINING_TYPE_UNIVERSITY]];
            }
            if ($settings->enable_training_type_practice) {
                $types[] = ['id' => Application::TRAINING_TYPE_PRACTICE, 'name' => Application::TRAINING_TYPES[Application::TRAINING_TYPE_PRACTICE]];
            }

            $this->trainingTypes = collect($types);

            // Auto-select if only one option
            if ($this->trainingTypes->count() === 1) {
                $this->trainingType = $this->trainingTypes->first()['id'] ?? 0;
            }
        } catch (Exception $e) {
            $this->logException('Training Type Load Error', $e);
        }
    }

    private function loadGovernoratesIfNeeded(): void
    {
        if ($this->governorates->isEmpty()) {
            try {
                $this->governorates = Governorate::select('id', 'name')
                    ->orderBy('name')
                    ->get()
                    ->map(fn($gov) => ['id' => $gov->id, 'name' => $gov->name]);
            } catch (Exception $e) {
                $this->logException('Failed to load governorates', $e);
            }
        }
    }

    private function loadInstitutions(): void
    {
        try {
            $this->institutions = Institution::where('is_active', true)
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
                ->map(fn($inst) => ['id' => $inst->id, 'name' => $inst->name]);
        } catch (Exception $e) {
            $this->logException('Failed to load institutions', $e);
        }
    }

    private function loadAdministratives(): void
    {
        try {
            $query = Administrative::query();

            // Filter by medical if training type is practice
            if ($this->trainingType === Application::TRAINING_TYPE_PRACTICE) {
                $query->where('is_medical', true);
            }

            $this->administratives = $query
                ->select('id', 'title as name')
                ->orderBy('id')
                ->get()
                ->map(fn($admin) => ['id' => $admin->id, 'name' => $admin->name]);

            Log::info('Administratives loaded', ['count' => $this->administratives->count(), 'trainingType' => $this->trainingType]);
        } catch (Exception $e) {
            $this->logException('Failed to load administratives', $e);
        }
    }

    private function loadAllSections(): void
    {
        try {
            // Load ALL sections with their relationships and capacity info
            $sections = Section::with(['department', 'administrative'])
                ->select('id', 'name_location', 'department_id', 'administrative_id', 'capacity')
                ->orderBy('id')
                ->get();

            // Filter by medical if training type is practice
            if ($this->trainingType === Application::TRAINING_TYPE_PRACTICE) {
                $sections = $sections->filter(fn($sec) => $sec->department?->is_medical);
            }

            $this->allSections = $sections
                ->map(function ($section) {
                    $stats = $section->getCapacityStats();
                    $isFull = $stats['is_full'] ?? false;

                    // Debug logging
                    Log::debug('Section capacity stats', [
                        'section_id' => $section->id,
                        'section_name' => $section->name_location,
                        'capacity' => $stats['total'],
                        'used' => $stats['used'],
                        'available' => $stats['available'],
                        'is_full' => $isFull,
                    ]);

                    return [
                        'id' => $section->id,
                        'name' => $section->name_location,
                        'departmentId' => $section->department_id,
                        'administrativeId' => $section->administrative_id,
                        'isFull' => $isFull,
                    ];
                })
                ->values();

            Log::info('All sections loaded', ['count' => $this->allSections->count(), 'trainingType' => $this->trainingType]);
        } catch (Exception $e) {
            $this->logException('Failed to load sections', $e);
        }
    }

    private function loadAllDepartments(): void
    {
        try {
            // Load ALL departments for client-side filtering
            $query = Department::query();

            // Filter by medical if training type is practice
            if ($this->trainingType === Application::TRAINING_TYPE_PRACTICE) {
                $query->where('is_medical', true);
            }

            $this->allDepartments = $query
                ->select('id', 'title as name')
                ->orderBy('id')
                ->get()
                ->map(fn($dept) => [
                    'id' => $dept->id,
                    'name' => $dept->name,
                ])
                ->values();

            Log::info('All departments loaded', ['count' => $this->allDepartments->count(), 'trainingType' => $this->trainingType]);
        } catch (Exception $e) {
            $this->logException('Failed to load departments', $e);
        }
    }

    private function loadAllMajors(): void
    {
        try {
            // Load ALL majors with college relationships for client-side filtering
            $this->allMajors = Major::with(['colleges'])
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
                ->map(fn($major) => [
                    'id' => $major->id,
                    'name' => $major->name,
                    'collegeIds' => $major->colleges->pluck('id')->toArray(),
                    'institutionIds' => $major->colleges->pluck('institution_id')->unique()->toArray(),
                ])
                ->values();

            Log::info('All majors loaded', ['count' => $this->allMajors->count()]);
        } catch (Exception $e) {
            $this->logException('Failed to load majors', $e);
        }
    }

    private function loadCollegeId(): void
    {
        if (!$this->majorId) {
            $this->collegeId = null;
            return;
        }

        try {
            // Get college related to this major and institution
            $college = College::whereHas('majors', function ($q) {
                $q->where('major_id', $this->majorId);
            })
                ->where('institution_id', $this->institutionId)
                ->first();

            $this->collegeId = $college ? $college->id : null;
        } catch (Exception $e) {
            $this->logException('Failed to load college data', $e);
        }
    }

    // ========================================
    // VALIDATION CHECKS
    // ========================================
    private function checkApplicationStatus(): void
    {
        if (strlen($this->nationalId ?? '') !== 9 || !$this->trainingType) {
            return;
        }

        $this->isValidating = true;

        try {
            // Check cache first
            $cacheKey = "app_status:{$this->nationalId}:{$this->trainingType}";
            $cachedResult = Cache::get($cacheKey);

            if ($cachedResult !== null) {
                Log::debug('Using cached application status result', [
                    'nationalId' => $this->nationalId,
                    'trainingType' => $this->trainingType,
                    'result' => $cachedResult
                ]);
                $this->processApplicationStatusResult($cachedResult);
                return;
            }

            Log::debug('Checking application status', [
                'nationalId' => $this->nationalId,
                'trainingType' => $this->trainingType
            ]);

            // Query database directly for application check
            $trainee = Trainee::where('national_id', $this->nationalId)->first();

            if (!$trainee) {
                // No trainee found - allow to proceed
                Log::debug('No trainee found, allowing to proceed', [
                    'nationalId' => $this->nationalId
                ]);
                $this->clearStatusMessage();
                $this->showPersonalDetails = true;
                $this->showTrainingDetails = true;
                return;
            }

            Log::debug('Trainee found, checking applications', [
                'traineeId' => $trainee->id,
                'trainingType' => $this->trainingType
            ]);

            // Check settings for re-application policy
            $settings = app(TrainingSettings::class);
            $canReapply = ($this->trainingType === Application::TRAINING_TYPE_UNIVERSITY)
                ? $settings->can_university_reapply
                : $settings->can_practice_reapply;

            // Build query for existing applications
            $query = Application::where('trainee_id', $trainee->id)
                ->where('training_type', $this->trainingType)
                ->whereNull('deleted_at');

            if ($canReapply) {
                // If re-application allowed, only block if there's an application NOT in Ended status
                $blockingApplication = $query->where('status', '!=', Application::STATUS_ENDED_TRAINING)->first();
            } else {
                // If NOT allowed, block if ANY application exists
                $blockingApplication = $query->first();
            }

            // Prepare result
            if ($blockingApplication) {
                Log::info('Blocking application found', [
                    'applicationId' => $blockingApplication->id,
                    'status' => $blockingApplication->status
                ]);
                $result = [
                    'has_application' => true,
                    'status' => $blockingApplication->status,
                    'message' => $this->getApplicationStatusMessage($blockingApplication),
                ];
                // Cache for configured TTL (from TraineeFormConfig)
                Cache::put($cacheKey, $result, now()->addMinutes(TraineeFormConfig::CACHE_TTL_MINUTES));
                $this->processApplicationStatusResult($result);
            } else {
                Log::debug('No blocking application found, allowing to proceed', [
                    'traineeId' => $trainee->id,
                    'trainingType' => $this->trainingType
                ]);
                $result = [
                    'has_application' => false,
                    'status' => null,
                ];
                // Cache for configured TTL (from TraineeFormConfig)
                Cache::put($cacheKey, $result, now()->addMinutes(TraineeFormConfig::CACHE_TTL_MINUTES));
                $this->clearStatusMessage();
                $this->showPersonalDetails = true;
                $this->showTrainingDetails = true;
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
            $statusText = $result['message'] ?? 'لا يمكنك تقديم طلب جديد في هذا الوقت';
            $this->setStatusMessage($statusText, 'error');
            $this->dispatchToast($statusText, 'error');
            $this->showPersonalDetails = false;
            $this->showTrainingDetails = false;
            $this->termsApproval = false;
        } else {
            $this->clearStatusMessage();
            $this->showPersonalDetails = true;
            $this->showTrainingDetails = true;
        }
    }

    private function getApplicationStatusMessage(Application $application): string
    {
        return \App\Models\Application::getStatusMessage($application->status, $application->training_type);
    }

    private function setStatusMessage(string $text, string $type = 'note'): void
    {
        $this->statusMessage = $text;
        $this->statusMessageType = $type;
    }

    private function clearStatusMessage(): void
    {
        $this->statusMessage = '';
        $this->statusMessageType = 'note';
    }

    private function dispatchToast(string $message, string $type = 'success'): void
    {
        // Dispatch to browser in Livewire 3 format
        $this->dispatch('show-toast', type: $type, message: $message);
    }

    private function prefillForm(array $trainee): void
    {
        $this->fullName = $trainee['full_name'] ?? null;
        $this->fullNameReadonly = true;

        $this->dob = $trainee['dob'] ?? null;
        $this->dobReadonly = true;

        $this->nationalIdReadonly = true;

        $this->phoneNumber = $trainee['phone_number'] ?? null;
        $this->governorateId = !empty($trainee['governorate_id']) ? (int) $trainee['governorate_id'] : null;
        $this->street = $trainee['street'] ?? null;
        $this->institutionId = !empty($trainee['institution_id']) ? (int) $trainee['institution_id'] : null;
        $this->majorId = !empty($trainee['major_id']) ? (int) $trainee['major_id'] : null;
        $this->trainingHours = !empty($trainee['training_hours']) ? (int) $trainee['training_hours'] : null;

        // Load filtered lists if needed
        if ($this->institutionId) {
            $this->loadFilteredMajors();
        }

        // Show personal details since we have data
        $this->showPersonalDetails = true;
        $this->showTrainingDetails = true;
    }

    private function resetFormRestrictions(): void
    {
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
    private function toggleUniversityFields(): void
    {
        $isUniversity = $this->trainingType === Application::TRAINING_TYPE_UNIVERSITY;

        if (!$isUniversity) {
            // Reset university fields if not university training
            $this->institutionId = null;
            $this->majorId = null;
            $this->collegeId = null;
            $this->majors = collect();
        }
    }

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

    // ========================================
    // FORM SUBMISSION
    // ========================================
    public function submit()
    {
        try {
            // Validate all fields
            $validated = $this->validate();

            // Check for training type specific re-application policy
            $settings = app(TrainingSettings::class);
            $canReapply = ($this->trainingType == Application::TRAINING_TYPE_UNIVERSITY)
                ? $settings->can_university_reapply
                : $settings->can_practice_reapply;

            // Double check existing application status (server-side)
            $trainee = Trainee::where('national_id', $this->nationalId)->first();
            if ($trainee) {
                $query = Application::where('trainee_id', $trainee->id)
                    ->where('training_type', $this->trainingType)
                    ->whereNull('deleted_at');

                if ($canReapply) {
                    $hasActiveApp = $query->where('status', '!=', Application::STATUS_ENDED_TRAINING)->exists();
                    if ($hasActiveApp) {
                        $this->showError('لديك بالفعل طلب تدريب قيد المعالجة. لا يمكنك التقديم مجدداً حتى ينتهي التدريب الحالي.');
                        return;
                    }
                } else {
                    if ($query->exists()) {
                        $this->showError('لديك بالفعل تطبيق تدريب من هذا النوع.');
                        return;
                    }
                }
            }

            // Verify section capacity one last time
            $section = Section::find($this->sectionId);
            if ($section && ($section->getCapacityStats()['is_full'] ?? false)) {
                if (!$settings->hide_full_sections) {
                    $this->showError('نعتذر، هذا القسم ممتلئ حالياً. يرجى اختيار قسم آخر.');
                    return;
                }
            }

            try {
                // Use transaction to ensure data integrity
                $result = DB::transaction(function () use ($settings) {
                    // 1. Infer college_id if missing
                    if (empty($this->collegeId) && !empty($this->majorId)) {
                        $major = Major::find($this->majorId);
                        if ($major) {
                            $firstCollege = $major->colleges()->first();
                            if ($firstCollege) {
                                $this->collegeId = $firstCollege->id;
                                $this->institutionId = $firstCollege->institution_id ?? $this->institutionId;
                            }
                        }
                    }

                    // 2. Create or update trainee record
                    $trainee = Trainee::updateOrCreate(
                        ['national_id' => $this->nationalId],
                        [
                            'full_name' => $this->fullName,
                            'phone_number' => $this->phoneNumber,
                            'dob' => $this->dob,
                            'governorate_id' => $this->governorateId,
                            'street' => $this->street,
                            'institution_id' => $this->institutionId ?: null,
                            'college_id' => $this->collegeId ?: null,
                            'major_id' => $this->majorId ?: null,
                            'training_hours' => $this->trainingHours,
                        ]
                    );

                    // 3. Create application record
                    $application = Application::create([
                        'trainee_id' => $trainee->id,
                        'department_id' => $this->departmentId,
                        'administrative_id' => $this->administrativeId,
                        'section_id' => $this->sectionId,
                        'street' => $this->street,
                        'training_type' => $this->trainingType,
                        'status' => Application::STATUS_NEW,
                    ]);

                    // 4. Handle file upload (if present)
                    if ($this->letterFile) {
                        $extension = $this->letterFile->getClientOriginalExtension();
                        $customFileName = "{$application->id}_{$trainee->id}.{$extension}";
                        $letterPath = $this->letterFile->storeAs('application-letters', $customFileName, 'public');

                        $application->update(['application_letter' => $letterPath]);
                    }

                    return $application;
                });

                if ($result) {
                    $this->setMessage('تم إرسال الطلب بنجاح. جاري التحويل...', 'success');
                    $this->resetForm();
                    return redirect()->route('training.welcome');
                }
            } catch (Exception $e) {
                $this->logException('Database Transaction Error', $e);
                $this->showError('حدث خطأ أثناء حفظ الطلب. يرجى المحاولة مرة أخرى.');
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


    private function resetForm(): void
    {
        $this->reset([
            'fullName',
            'nationalId',
            'phoneNumber',
            'dob',
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
        ]);
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
            'isUniversity' => $this->trainingType === Application::TRAINING_TYPE_UNIVERSITY,
            'isLoading' => $this->isValidating,
        ]);
    }
}

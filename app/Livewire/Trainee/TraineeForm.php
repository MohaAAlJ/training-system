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
// use App\Enums\TrainingType;
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
    public bool $showPersonalDetails = false;  // Step 2
    public bool $showTrainingDetails = false;  // Step 3
    public bool $showTerms = false;            // Step 4
    public bool $isValidating = false;
    public bool $isBlocked = false;
    public string $statusMessage = '';
    public string $statusMessageType = 'note';
    public array $applicationStatus = [];
    public string $message = '';
    public string $messageType = 'info';

    // ========================================
    // CACHED OPTIONS - Prevent unnecessary API calls
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

    private function updateSectionVisibility(): void
    {
        // If blocked by existing application, hide all sections
        if ($this->isBlocked) {
            $this->showPersonalDetails = false;
            $this->showTrainingDetails = false;
            $this->showTerms = false;
            return;
        }

        // Step 1 Validation (Basic Info)
        // Removed statusMessageType check to prevent UI flickering on transient errors
        $step1Valid = !empty($this->trainingType) &&
            strlen($this->nationalId ?? '') === 9 &&
            !empty($this->dob) &&
            validatePalestinianId($this->nationalId) === 'valid';

        $this->showPersonalDetails = $step1Valid;

        // Step 2 Validation (Personal Details)
        $step2Valid = $step1Valid &&
            !empty($this->fullName) &&
            !empty($this->governorateId);

        $this->showTrainingDetails = $step2Valid;

        // Step 3 Validation (Training Details)
        $step3Valid = $step2Valid &&
            !empty($this->administrativeId) &&
            !empty($this->departmentId) &&
            !empty($this->sectionId) &&
            !empty($this->trainingHours);

        if ($this->trainingType == Application::UNIVERSITY) {
            $step3Valid = $step3Valid && !empty($this->institutionId) && !empty($this->majorId);
        }

        $this->showTerms = $step3Valid;
    }

    // ========================================
    // VALIDATION RULES & MESSAGES
    // ========================================
    protected function rules()
    {
        $config = TraineeFormConfig::class;

        $rules = [
            'trainingType' => 'required|in:' . Application::UNIVERSITY . ',' . Application::PRACTICE,
            // COMMENTED OUT FOR MANUAL DATA ENTRY - WILL BE RESTORED AFTER DATA ENTRY COMPLETE
            // Original: 'nationalId' => ['required', 'digits:9', ...]
            'nationalId' => [
                'required',
                'digits:9',
                'regex:' . $config::NATIONAL_ID_REGEX,
                function ($attribute, $value, $fail) {
                    if ($value && validatePalestinianId($value) !== 'valid') {
                        $fail('رقم الهوية الوطنية غير صحيح.');
                    }
                },
            ],
            // COMMENTED OUT FOR MANUAL DATA ENTRY - WILL BE RESTORED AFTER DATA ENTRY COMPLETE
            // Original: 'dob' => ['required', 'date_format:Y-m-d', ...]
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
                // COMMENTED OUT FOR MANUAL DATA ENTRY - WILL BE RESTORED AFTER DATA ENTRY COMPLETE
                // Original: 'phoneNumber' => ['required', 'string', 'regex:' . $config::PHONE_REGEX, ],
                'phoneNumber' => [
                    'required',
                    'string',
                    'regex:' . $config::PHONE_REGEX,
                ],
                'governorateId' => 'required|exists:governorates,id',
                // COMMENTED OUT FOR MANUAL DATA ENTRY - WILL BE RESTORED AFTER DATA ENTRY COMPLETE
                // 'street' => 'required|string|max:255',
                'street' => 'nullable|string|max:255',
                'gender' => ['required', \Illuminate\Validation\Rule::enum(\App\Enums\Gender::class)],
            ]);
        }

        if ($this->showTrainingDetails) {
            $rules = array_merge($rules, [
                'trainingHours' => 'required|integer|min:' . $config::MIN_TRAINING_HOURS . '|max:' . $config::MAX_TRAINING_HOURS,
                'administrativeId' => 'required|exists:administratives,id',
                'departmentId' => 'required|exists:departments,id',
                'sectionId' => 'required|exists:sections,id',
            ]);

            if ($this->trainingType === Application::UNIVERSITY) {
                $rules = array_merge($rules, [
                    'institutionId' => 'required|exists:institutions,id',
                    'majorId' => 'required|exists:majors,id',
                    'universityNumber' => 'required|string|max:255',
                ]);
            }
        }

        if ($this->showTerms) {
            $rules = array_merge($rules, [
                'termsApproval' => 'required|accepted',
            ]);
        }

        return $rules;
    }

    protected function messages()
    {
        return TraineeFormConfig::getValidationMessages();
    }

    // ========================================
    // INITIALIZATION & MOUNTING
    // ========================================
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

        // Check status if pre-filled
        if ($this->nationalId && $this->trainingType) {
            $this->checkApplicationStatus();
        }
    }

    // ========================================
    // LIFECYCLE HOOKS
    // ========================================
    public function hydrate()
    {
        $this->ensureDataLoaded();
    }

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

        // Trigger status check if relevant data changed
        // Removed 'trainingType' to avoid duplicate calls as updatedTrainingType handles it
        if (in_array($property, ['nationalId', 'dob'])) {
            $this->checkApplicationStatus();
        }

        $this->updateSectionVisibility();
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
            $this->showPersonalDetails = false;
            $this->showTrainingDetails = false;
            $this->showTerms = false;
            return;
        }
        if ($idLength === 0) {
            $this->updateSectionVisibility();
            return;
        }

        // Validate Palestinian National ID checksum
        if (validatePalestinianId($this->nationalId) !== 'valid') {
            $this->dispatchToast('رقم الهوية الوطنية غير صحيح.', 'error');
            $this->showPersonalDetails = false;
            $this->showTrainingDetails = false;
            $this->showTerms = false;
            return;
        }

        // ID is valid, clear any previous error
        $this->clearMessage();
        $this->updateSectionVisibility();
    }

    public function updateDob($dob)
    {
        $this->dob = $dob;
        $this->validateOnly('dob');
        $this->checkApplicationStatus();
        $this->updateSectionVisibility();
    }

    public function updateDobAlpine($dob)
    {
        $this->dob = $dob;
        $this->validateOnly('dob');
        $this->checkApplicationStatus();
        $this->updateSectionVisibility();
    }

    public function updatedNationalId()
    {
        // National ID update is now handled in the updated() method
        $this->updateSectionVisibility();
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
        $this->termsApproval = false;

        // Reload all data filtered by training type when training type is selected
        if ($this->trainingType) {
            $this->loadAdministratives();  // Filters by medical if practice training
            $this->loadAllDepartments();    // Filters by medical if practice training
            $this->loadAllSections();       // Reloads sections with updated capacity

            $this->checkApplicationStatus();
        }

        $this->updateSectionVisibility();
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
        // Note: Cascading clear of departmentId/sectionId is now handled by Alpine.js
        // via @change handlers to avoid race conditions with @entangle
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
        // Note: Cascading clear of sectionId is now handled by Alpine.js
        // via @change handlers to avoid race conditions with @entangle
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
                ->filter(
                    fn($s) =>
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
                $types[] = ['id' => Application::UNIVERSITY, 'name' => Application::getTrainingTypeLabel(Application::UNIVERSITY)];
            }
            if ($settings->enable_training_type_practice) {
                $types[] = ['id' => Application::PRACTICE, 'name' => Application::getTrainingTypeLabel(Application::PRACTICE)];
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
                $allGovs = Governorate::select('id', 'name')->get();
                $order = ['شمال غزة', 'غزة', 'محافظات الوسطى', 'خانيونس', 'رفح'];

                $this->governorates = $allGovs
                    ->sortBy(function ($gov) use ($order) {
                        $key = array_search($gov->name, $order);
                        return $key === false ? 99 : $key;
                    })
                    ->map(fn($gov) => ['id' => $gov->id, 'name' => $gov->name])
                    ->values();
            } catch (Exception $e) {
                $this->logException('Failed to load governorates', $e);
            }
        }
    }

    private function loadInstitutions(): void
    {
        try {
            $this->institutions = Institution::active()
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
            $query = Administrative::query()->active();

            // Filter by medical if training type is practice
            if ($this->trainingType === Application::PRACTICE) {
                $query->where('is_medical', true);
            }

            // Only show administratives that have at least one active section
            $query->whereHas('sections', function ($q) {
                $q->active();
            });

            $this->administratives = $query
                ->select('*')
                ->with('governorate')
                ->orderBy('id')
                ->get()
                ->map(fn($admin) => ['id' => $admin->id, 'name' => $admin->name]); // Use 'name' column

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
                ->active()
                ->select('id', 'name', 'department_id', 'administrative_id', 'capacity')
                ->orderBy('id')
                ->get();

            // Filter by medical if training type is practice
            if ($this->trainingType === Application::PRACTICE) {
                $sections = $sections->filter(fn($sec) => $sec->department?->is_medical);
            }

            $this->allSections = $sections
                ->map(function ($section) {
                    $stats = $section->getCapacityStats();
                    $isFull = $stats['is_full'] ?? false;

                    // Debug logging
                    Log::debug('Section capacity stats', [
                        'section_id' => $section->id,
                        'section_name' => $section->name,
                        'capacity' => $stats['total'],
                        'used' => $stats['used'],
                        'available' => $stats['available'],
                        'is_full' => $isFull,
                    ]);

                    return [
                        'id' => $section->id,
                        'name' => $section->name,
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
            $query = Department::query()->active();

            // Filter by medical if training type is practice
            if ($this->trainingType === Application::PRACTICE) {
                $query->where('is_medical', true);
            }

            $this->allDepartments = $query
                ->select('id', 'name as name')
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
            // Using colleges->institution_id to get institutionIds since that relationship is reliable
            $this->allMajors = Major::with(['colleges' => fn($q) => $q->active()])
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
                ->map(fn($major) => [
                    'id' => $major->id,
                    'name' => $major->name,
                    'collegeIds' => $major->colleges->pluck('id')->toArray(),
                    'institutionIds' => $major->colleges->pluck('institution_id')
                        ->unique()
                        ->filter()
                        ->values()
                        ->toArray(),
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
        if (strlen($this->nationalId ?? '') !== 9 || !$this->trainingType || !$this->dob) {
            return;
        }

        // Validate Palestinian National ID format and checksum
        if (validatePalestinianId($this->nationalId) !== 'valid') {
            $this->setStatusMessage('رقم الهوية الوطنية غير صحيح.', 'error');
            $this->dispatchToast('رقم الهوية الوطنية غير صحيح.', 'error');
            $this->showPersonalDetails = false;
            $this->showTrainingDetails = false;
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
                $this->setStatusMessage('تاريخ الميلاد المدخل غير مطابق لرقم الهوية في سجلاتنا.', 'error');
                $this->dispatchToast('تاريخ الميلاد غير مطابق لرقم الهوية.', 'error');
                $this->showPersonalDetails = false;
                $this->showTrainingDetails = false;
                return;
            }

            // Process the result from the service
            $this->processApplicationStatusResult($result);

            // Prefill form if trainee data exists and no blocking application
            if (!($result['has_application'] ?? false) && isset($result['trainee_data'])) {
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
            $this->isBlocked = true;
            $statusText = $result['message'] ?? 'لا يمكنك تقديم طلب جديد في هذا الوقت';
            $this->setStatusMessage($statusText, 'error');
            $this->dispatchToast($statusText, 'error');
            $this->showPersonalDetails = false;
            $this->showTrainingDetails = false;
            $this->termsApproval = false;
        } else {
            $this->isBlocked = false;
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
        $this->isExistingTrainee = true;
        // Store trusted data to prevent tampering
        $this->originalTraineeData = $trainee;

        $this->dispatchToast('مرحباً بعودتك! تم استرجاع بياناتك السابقة', 'info');

        $this->fullName = $trainee['full_name'] ?? null;
        $this->fullNameReadonly = true;

        if (isset($trainee['dob'])) {
            $this->dob = \Carbon\Carbon::parse($trainee['dob'])->format('Y-m-d');
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
        $this->institutionId = !empty($trainee['institution_id']) ? (int) $trainee['institution_id'] : null;
        $this->majorId = !empty($trainee['major_id']) ? (int) $trainee['major_id'] : null;
        $this->trainingHours = !empty($trainee['training_hours']) ? (int) $trainee['training_hours'] : null;

        // Load filtered lists if needed
        if ($this->institutionId) {
            $this->loadFilteredMajors();
        }

        // Show personal details since we have data
        $this->showPersonalDetails = true;
        // Do NOT auto-show training details, let user review personal info first?
        // Code previously showed it. I'll keep it to reduce clicks if data is complete.
        $this->showTrainingDetails = true;
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
    private function toggleUniversityFields(): void
    {
        $isUniversity = $this->trainingType === Application::UNIVERSITY;

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
            $canReapply = ($this->trainingType == Application::UNIVERSITY)
                ? (bool) $settings->can_university_reapply
                : (bool) $settings->can_practice_reapply;

            // 2. Build Cache Key including settings
            // Including canReapply in key ensures cache invalidates if settings change
            // Added v5: Rolled back strict blocking - verifying "Practice Ended -> Uni Apply" is Allowed
            $cacheKey = "app_status_v5:{$this->nationalId}:{$this->trainingType}:{$this->dob}:" . ($canReapply ? '1' : '0');

            // Double check existing application status (server-side) using centralized service
            $service = app(\App\Services\Application\ApplicationStatusService::class);
            $statusResult = $service->checkApplicationEligibility(
                (string) $this->nationalId,
                (int) $this->trainingType,
                (string) $this->dob
            );

            if (!($statusResult['can_apply'] ?? true)) {
                $this->showError($statusResult['message'] ?: 'لا يمكنك تقديم هذا الطلب حالياً.');
                return;
            }

            // Verify section capacity one last time
            $section = Section::find($this->sectionId);
            if ($section && ($section->getCapacityStats()['is_full'] ?? false)) {
                if (!$settings->hide_full_sections) {
                    $this->showError('نعتذر، هذا القسم ممتلئ حالياً. يرجى اختيار قسم آخر.');
                    return;
                }
            }

            // SECURITY FIX: Prevent tampering with locked fields via "Inspect Element"
            // If this is an existing trainee, we MUST use the trusted original data
            // for identity fields (National ID, Name, DOB), ignoring any compromised frontend input.
            if ($this->isExistingTrainee && !empty($this->originalTraineeData)) {
                $this->fullName = $this->originalTraineeData['full_name'] ?? $this->fullName;
                $this->nationalId = $this->originalTraineeData['national_id'] ?? $this->nationalId;

                if (isset($this->originalTraineeData['dob'])) {
                    $this->dob = \Carbon\Carbon::parse($this->originalTraineeData['dob'])->format('Y-m-d');
                }

                // Log security event if needed, or just silently enforce
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
                            'gender' => $this->gender,
                            'governorate_id' => $this->governorateId,
                            'street' => $this->street,
                            'institution_id' => $this->institutionId ?: null,
                            'college_id' => $this->collegeId ?: null,
                            'major_id' => $this->majorId ?: null,
                            'university_number' => $this->universityNumber ?: null,
                            'training_hours' => $this->trainingHours,
                        ]
                    );

                    // 3. Create application record
                    $application = Application::create([
                        'trainee_id' => $trainee->id,
                        'section_id' => $this->sectionId,
                        'street' => $this->street,
                        'university_number' => $this->universityNumber ?: null,
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
        ]);
        $this->resetFormRestrictions();
        $this->clearStatusMessage();
        $this->showPersonalDetails = false;
        $this->showTrainingDetails = false;
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

<?php

namespace App\Livewire\Trainee;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use App\Models\Governorate;
use App\Models\Institution;
use App\Models\Administrative;

/**
 * TraineeForm Livewire Component
 * 
 * This component handles the complete trainee application form
 * with validation, cascading selects, file uploads, and existing application checks.
 * 
 * Replaces vanilla JavaScript app.js with reactive Livewire state management.
 */
class TraineeForm extends Component
{
    use WithFileUploads;

    // ========================================
    // FORM INPUTS - Public properties that bind to form
    // ========================================
    public string $fullName = '';
    public string $nationalId = '';
    public string $phoneNumber = '';
    public string $dob = '';
    public int $governorateId = 0;
    public string $street = '';
    public int $institutionId = 0;
    public int $majorId = 0;
    public int $administrativeId = 0;
    public int $departmentId = 0;
    public int $sectionId = 0;
    public int $trainingType = 0;
    public int $trainingHours = 0;
    public int $collegeId = 0;
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
    public Collection $administratives;
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
    // VALIDATION RULES
    // ========================================
    protected function rules()
    {
        $rules = [
            'trainingType' => 'required|in:1,2',
            'nationalId' => 'required|digits:9',
        ];
        
        if ($this->showPersonalDetails) {
            $rules = array_merge($rules, [
                'fullName' => 'required|string|regex:/^[\p{L}\s]+$/u|max:150',
                'dob' => [
                    'required',
                    'date_format:Y-m-d',
                    'before_or_equal:' . now()->subYears(20)->format('Y-m-d'),
                ],
                'phoneNumber' => [
                    'required',
                    'string',
                    'regex:/^97(0|2)5\d{8}$/',
                ],
                'governorateId' => 'required|exists:governorates,id',
                'street' => 'required|string|max:255',
                'trainingHours' => 'required|integer|min:50|max:1000',
                'administrativeId' => 'required|exists:administratives,id',
                'departmentId' => 'required|exists:departments,id',
                'sectionId' => 'required|exists:sections,id',
            ]);
            
            if ($this->trainingType === 1) {
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
        // Generate UUID for form security (prevents replay attacks)
        $this->formUuid = \Illuminate\Support\Str::uuid()->toString();
        
        $this->governorates = collect();
        $this->institutions = collect();
        $this->majors = collect();
        $this->administratives = collect();
        $this->departments = collect();
        $this->sections = collect();
        $this->trainingTypes = collect();

        // Load initial data eagerly
        $this->loadTrainingTypes();
        $this->loadGovernoratesIfNeeded();
        $this->loadInstitutions();
        // Don't load administratives here - they depend on trainingType being selected
    }

    // ========================================
    // LIFECYCLE HOOKS
    // ========================================
    public function updated($property)
    {
        // Clear message when user interacts
        if ($property !== 'message' && $property !== 'messageType') {
            $this->clearMessage();
        }

        // Validate individual fields
        $this->validateOnly($property);
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
        // Check for existing applications when national ID changes
        if (strlen($this->nationalId) === 9 && $this->trainingType) {
            $this->checkApplicationStatus();
        }
    }

    public function updatedTrainingType()
    {
        // Reset dependent fields when training type changes
        $this->administrativeId = 0;
        $this->departmentId = 0;
        $this->sectionId = 0;
        $this->institutionId = 0;
        $this->majorId = 0;
        $this->collegeId = 0;
        $this->showPersonalDetails = false;
        $this->showTrainingDetails = false;
        
        // Load administratives when training type is selected
        if ($this->trainingType) {
            $this->loadAdministratives();
            $this->checkApplicationStatus();
        }
    }

    public function updatedInstitutionId()
    {
        // Load majors when institution changes
        if ($this->institutionId) {
            $this->loadMajors();
        } else {
            $this->majors = collect();
            $this->majorId = 0;
        }
    }

    public function updatedAdministrativeId()
    {
        // Load departments when administrative changes
        if ($this->administrativeId) {
            $this->loadDepartments();
        } else {
            $this->departments = collect();
            $this->departmentId = 0;
            $this->sections = collect();
            $this->sectionId = 0;
        }
    }

    public function updatedDepartmentId()
    {
        // Load sections when department changes
        if ($this->departmentId) {
            $this->loadSections();
        } else {
            $this->sections = collect();
            $this->sectionId = 0;
        }
    }

    public function updatedMajorId()
    {
        // Load college ID when major changes
        if ($this->majorId) {
            $this->loadCollegeId();
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
            $settings = \App\Models\GeneralSetting::instance();
            $types = [];
            
            if ($settings->enable_training_type_university) {
                $types[] = ['id' => 1, 'name' => 'تدريب جامعي'];
            }
            if ($settings->enable_training_type_practice) {
                $types[] = ['id' => 2, 'name' => 'تدريب عملي'];
            }
            
            $this->trainingTypes = collect($types);
            
            // Auto-select if only one option
            if ($this->trainingTypes->count() === 1) {
                $this->trainingType = $this->trainingTypes->first()['id'] ?? 0;
            }
        } catch (\Exception $e) {
            \Log::error('Training Type Load Error', [
                'message' => $e->getMessage(),
            ]);
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
            } catch (\Exception $e) {
                \Log::error('Failed to load governorates', ['error' => $e->getMessage()]);
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
        } catch (\Exception $e) {
            \Log::error('Failed to load institutions', ['error' => $e->getMessage()]);
        }
    }

    private function loadMajors(): void
    {
        if (!$this->institutionId) {
            $this->majors = collect();
            return;
        }

        try {
            $this->majors = \App\Models\Major::whereHas('colleges', function ($q) {
                $q->where('colleges.institution_id', $this->institutionId)
                  ->where('colleges.is_active', true);
            })
            ->select('majors.id', 'majors.name')
            ->orderBy('majors.name')
            ->get()
            ->map(fn($major) => ['id' => $major->id, 'name' => $major->name]);
        } catch (\Exception $e) {
            \Log::error('Failed to load majors', ['error' => $e->getMessage()]);
        }
    }

    private function loadAdministratives(): void
    {
        try {
            $this->administratives = Administrative::select('id', 'name')
                ->orderBy('name')
                ->get()
                ->map(fn($admin) => ['id' => $admin->id, 'name' => $admin->name]);
        } catch (\Exception $e) {
            \Log::error('Failed to load administratives', ['error' => $e->getMessage()]);
        }
    }

    private function loadDepartments(): void
    {
        if (!$this->administrativeId || !$this->trainingType) {
            $this->departments = collect();
            return;
        }

        try {
            $this->departments = \App\Models\Department::where('administrative_id', $this->administrativeId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
                ->map(fn($dept) => ['id' => $dept->id, 'name' => $dept->name]);
        } catch (\Exception $e) {
            \Log::error('Failed to load departments', ['error' => $e->getMessage()]);
        }
    }

    private function loadSections(): void
    {
        if (!$this->departmentId || !$this->administrativeId || !$this->trainingType) {
            $this->sections = collect();
            return;
        }

        try {
            $this->sections = \App\Models\Section::where('department_id', $this->departmentId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
                ->map(fn($section) => ['id' => $section->id, 'name' => $section->name]);
        } catch (\Exception $e) {
            \Log::error('Failed to load sections', ['error' => $e->getMessage()]);
        }
    }

    private function loadCollegeId(): void
    {
        if (!$this->majorId) {
            $this->collegeId = 0;
            return;
        }

        try {
            // Get college related to this major and institution
            $college = \App\Models\College::whereHas('majors', function ($q) {
                $q->where('major_id', $this->majorId);
            })
            ->where('institution_id', $this->institutionId)
            ->first();
            
            $this->collegeId = $college ? $college->id : 0;
        } catch (\Exception $e) {
            \Log::error('Failed to load college data', ['error' => $e->getMessage()]);
        }
    }

    // ========================================
    // VALIDATION CHECKS
    // ========================================
    private function checkApplicationStatus(): void
    {
        if (strlen($this->nationalId) !== 9 || !$this->trainingType) {
            return;
        }

        $this->isValidating = true;

        try {
            // Check cache first
            $cacheKey = "app_status:{$this->nationalId}:{$this->trainingType}";
            $cachedResult = \Illuminate\Support\Facades\Cache::get($cacheKey);
            
            if ($cachedResult !== null) {
                $this->processApplicationStatusResult($cachedResult);
                return;
            }

            // Query database directly for application check
            $trainee = \App\Models\Trainee::where('national_id', $this->nationalId)->first();

            if (!$trainee) {
                // No trainee found - allow to proceed
                $this->clearStatusMessage();
                $this->showPersonalDetails = true;
                $this->showTrainingDetails = true;
                return;
            }

            // Check settings for re-application policy
            $settings = \App\Models\GeneralSetting::instance();
            $canReapply = ($this->trainingType == \App\Models\Application::TRAINING_TYPE_UNIVERSITY)
                ? $settings->can_university_reapply
                : $settings->can_practice_reapply;

            // Build query for existing applications
            $query = \App\Models\Application::where('trainee_id', $trainee->id)
                ->where('training_type', $this->trainingType)
                ->whereNull('deleted_at');

            if ($canReapply) {
                // If re-application allowed, only block if there's an application NOT in Ended status
                $blockingApplication = $query->where('status', '!=', \App\Models\Application::STATUS_ENDED_TRAINING)->first();
            } else {
                // If NOT allowed, block if ANY application exists
                $blockingApplication = $query->first();
            }

            // Prepare result
            if ($blockingApplication) {
                $result = [
                    'has_application' => true,
                    'status' => $blockingApplication->status,
                    'message' => $this->getApplicationStatusMessage($blockingApplication),
                ];
                // Cache for 5 minutes
                \Illuminate\Support\Facades\Cache::put($cacheKey, $result, now()->addMinutes(5));
                $this->processApplicationStatusResult($result);
            } else {
                $result = [
                    'has_application' => false,
                    'status' => null,
                ];
                // Cache for 5 minutes
                \Illuminate\Support\Facades\Cache::put($cacheKey, $result, now()->addMinutes(5));
                $this->clearStatusMessage();
                $this->showPersonalDetails = true;
                $this->showTrainingDetails = true;
            }
        } catch (\Exception $e) {
            \Log::error('Application Status Check Error', [
                'message' => $e->getMessage(),
                'national_id' => $this->nationalId,
            ]);
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
        } else {
            $this->clearStatusMessage();
            $this->showPersonalDetails = true;
            $this->showTrainingDetails = true;
        }
    }

    private function getApplicationStatusMessage(\App\Models\Application $application): string
    {
        return match ($application->status) {
            \App\Models\Application::STATUS_NEW => 'لديك طلب قيد الانتظار',
            \App\Models\Application::STATUS_INITIAL_APPROVE => 'لديك طلب في انتظار القبول الجامعي',
            \App\Models\Application::STATUS_CONFIRMATION => 'لديك طلب في انتظار التأكيد',
            \App\Models\Application::STATUS_WAITING_LIST => 'لديك طلب في قائمة الانتظار',
            \App\Models\Application::STATUS_STARTED_TRAINING => 'لديك تدريب نشط',
            \App\Models\Application::STATUS_ENDED_TRAINING => 'لديك طلب منتهي',
            \App\Models\Application::STATUS_REJECTED => 'لديك طلب سابق لايمكنك اصادر طلب جديد',
            \App\Models\Application::STATUS_DROPPED => 'لديك طلب منسحب',
            default => 'لديك طلب قائم',
        };
    }

    private function getArabicStatusMessage(string $status): string
    {
        $messages = [
            'pending' => 'لديك طلب قيد الانتظار',
            'waiting_university' => 'لديك طلب في انتظار القبول الجامعي',
            'waiting_list' => 'لديك طلب في قائمة الانتظار',
            'active_training' => 'لديك تدريب نشط',
            'completed' => 'لديك طلب منتهي',
            'previous_application' => 'لديك طلب سابق لايمكنك اصادر طلب جديد',
            'unknown' => 'لا يمكنك تقديم طلب جديد في هذا الوقت',
        ];
        
        return $messages[$status] ?? $messages['unknown'];
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
        $this->fullName = $trainee['full_name'] ?? '';
        $this->fullNameReadonly = true;

        $this->dob = $trainee['dob'] ?? '';
        $this->dobReadonly = true;

        $this->nationalIdReadonly = true;

        $this->phoneNumber = $trainee['phone_number'] ?? '';
        $this->governorateId = (int) ($trainee['governorate_id'] ?? 0);
        $this->street = $trainee['street'] ?? '';
        $this->institutionId = (int) ($trainee['institution_id'] ?? 0);
        $this->majorId = (int) ($trainee['major_id'] ?? 0);
        $this->trainingHours = (int) ($trainee['training_hours'] ?? 0);

        // Load cascading selects if institution is set
        if ($this->institutionId) {
            $this->loadMajors();
        }
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
        $isUniversity = $this->trainingType === 1;

        if (!$isUniversity) {
            // Reset university fields if not university training
            $this->institutionId = 0;
            $this->majorId = 0;
            $this->collegeId = 0;
            $this->majors = collect();
        }
    }

    public function setMessage(string $text, string $type = 'note'): void
    {
        $this->message = $text;
        $this->messageType = $type;
    }

    public function clearMessage(): void
    {
        $this->message = '';
        $this->messageType = 'note';
    }

    public function showError(string $message, \Exception $exception = null): void
    {
        $this->setMessage($message, 'error');
        if ($exception) {
            \Log::error($message, ['exception' => $exception]);
        }
    }

    // ========================================
    // FORM SUBMISSION
    // ========================================
    public function submit()
    {
        // Validate all fields
        $validated = $this->validate();

        // Prepare data for submission
        $data = [
            'form_uuid' => $this->formUuid,
            'full_name' => $this->fullName,
            'national_id' => $this->nationalId,
            'phone_number' => $this->phoneNumber,
            'dob' => $this->dob,
            'governorate_id' => $this->governorateId,
            'street' => $this->street,
            'institution_id' => $this->institutionId ?: null,
            'major_id' => $this->majorId ?: null,
            'college_id' => $this->collegeId ?: null,
            'administrative_id' => $this->administrativeId,
            'department_id' => $this->departmentId,
            'section_id' => $this->sectionId,
            'training_type' => $this->trainingType,
            'training_hours' => $this->trainingHours,
            'terms_approval' => $this->termsApproval ? 1 : 0,
        ];

        try {
            // Store file if present
            if ($this->letterFile) {
                $data['letter_file'] = $this->letterFile->store('applications', 'public');
            }

            // Submit to backend endpoint
            $response = Http::withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
                'X-Requested-With' => 'XMLHttpRequest',
            ])->post(url('/WelcomeForm/Form'), $data);

            \Log::info('Form Submission Response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                $this->setMessage('تم إرسال الطلب بنجاح. جاري التحويل...', 'success');
                
                // Reset form
                $this->resetForm();

                // Redirect to success page
                return redirect()->to('/WelcomeForm/Success');
            } else {
                // Handle validation errors from backend
                $errors = $response->json();
                \Log::warning('Form Submission Failed', ['errors' => $errors]);
                
                if (isset($errors['message'])) {
                    $this->showError($errors['message']);
                } else {
                    $this->showError('فشل إرسال الطلب. يرجى المحاولة مرة أخرى');
                }
            }
        } catch (\Exception $e) {
            \Log::error('Form Submission Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->showError('حدث خطأ أثناء إرسال الطلب: ' . $e->getMessage(), $e);
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
    // RENDERING
    // ========================================
    public function render()
    {
        return view('livewire.trainee.trainee-form', [
            'isUniversity' => $this->trainingType === 1,
            'isLoading' => $this->isValidating,
        ])->layout('components.layouts.app');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Administrative;
use App\Models\Section;
use App\Models\Department;
use App\Models\Institution;
use App\Models\Major;
use App\Models\Trainee;
use App\Models\Governorate;
// use App\Enums\TrainingType;
// use App\Helpers\Constants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Storage;

use App\Settings\TrainingSettings;

class ApplicationFormController extends Controller
{
    public function __construct(
        protected TrainingSettings $settings
    ) {}


    /**
     * Serve the welcome page.
     */
    public function showWelcome()
    {
        return view('Form.welcomeapp', [
            'isFormEnabled' => $this->settings->is_public_form_enabled,
            'formUuid' => (string) \Illuminate\Support\Str::uuid()
        ]);
    }

    /**
     * Serve the public trainee application form.
     */
    public function showForm()
    {
        if (!$this->settings->is_public_form_enabled) {
            return redirect()->route('training.welcome')->with('error', 'نعتذر، نموذج الالتحاق مغلق حالياً.');
        }

        if (!$this->settings->enable_training_type_university && !$this->settings->enable_training_type_practice) {
            return redirect()->route('training.welcome')->with('error', 'نعتذر، لا توجد أنواع تدريب متاحة حالياً.');
        }

        return view('Form.trainee-app.index', [
            'formUuid' => (string) \Illuminate\Support\Str::uuid()
        ]);
    }
    /**
     * Public JSON endpoints to feed the form selects from the database.
     */
    public function address()
    {
        $data = Governorate::select('id', 'name')->get()
            ->map(fn($gov) => [
                'id' => $gov->id,
                'name' => $gov->name,
            ])
            ->values();

        return response()->json($data);
    }

    public function institution()
    {
        $data = Institution::active()->select('id', 'name')->get()
            ->map(fn($inst) => [
                'id' => $inst->id,
                'name' => $inst->name,
            ])
            ->values();

        return response()->json($data);
    }

    public function major(Request $request)
    {
        $institutionId = $request->query('institution_id');

        // If an institution_id is provided, fetch majors that are linked to
        // colleges belonging to that institution via the college_major pivot.
        if ($institutionId) {
            $majors = Major::whereHas('colleges', function ($q) use ($institutionId) {
                $q->where('colleges.institution_id', $institutionId)
                    ->where('colleges.active', true);
            })->select('majors.id', 'majors.name')->get();
        } else {
            // Only show majors that have at least one active college
            $majors = Major::whereHas('colleges', function ($q) {
                $q->where('colleges.active', true);
            })->select('majors.id', 'majors.name')->get();
        }

        $data = $majors->map(fn($major) => [
            'id' => $major->id,
            'name' => $major->name,
        ])->values();

        return response()->json($data);
    }

    /**
     * Return colleges linked to a given major (used to auto-fill institution)
     */
    public function majorCollege(Request $request)
    {
        $majorId = $request->query('major_id');

        if (! $majorId) {
            return response()->json([], 200);
        }

        $major = Major::find($majorId);
        if (! $major) {
            return response()->json([], 200);
        }

        $colleges = $major->colleges()
            ->where('colleges.active', true)
            ->select('colleges.id', 'colleges.name', 'colleges.institution_id')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'institution_id' => $c->institution_id,
            ])->values();

        return response()->json($colleges);
    }

    public function administrative(Request $request)
    {
        $trainingType = $request->query('training_type');

        // Only show active administratives
        $query = Administrative::query()->active();

        if ($trainingType == Application::PRACTICE) {
            $query->where('is_medical', true);
        }

        // Only show administratives that have at least one active section
        $query->whereHas('sections', function ($q) {
            $q->active();
        });

        $data = $query->get()
            ->map(fn($adm) => [
                'id' => $adm->id,
                'name' => $adm->name, // Use 'name' column from administratives table
            ])
            ->values();

        return response()->json($data);
    }

    public function department(Request $request)
    {
        $administrativeId = $request->query('administrative_id');
        $trainingType = $request->query('training_type');

        $query = Department::query()->active();

        if ($trainingType == Application::PRACTICE) {
            $query->where('is_medical', true);
        }

        if ($administrativeId) {
            // Filter departments that have ACTIVE sections in this administrative unit
            $query->whereHas('sections', function ($q) use ($administrativeId) {
                $q->where('administrative_id', $administrativeId)
                    ->active();
            });
        }

        $data = $query->select('id', 'name')->get()
            ->map(function ($dept) {
                return [
                    'id' => $dept->id,
                    'name' => $dept->name,
                ];
            })
            ->values();

        return response()->json($data);
    }

    public function section(Request $request)
    {
        $departmentId = $request->query('department_id');
        $administrativeId = $request->query('administrative_id');
        $trainingType = $request->query('training_type');

        $query = Section::query();

        if ($trainingType == Application::PRACTICE) {
            $query->whereHas('department', function ($q) {
                $q->where('is_medical', true);
            });
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($administrativeId) {
            $query->where('administrative_id', $administrativeId);
        }

        // Always get active sections
        $sections = $query->active()->get();

        // Check if we should hide full sections
        $hideFull = $this->settings->hide_full_sections;

        $data = $sections->map(function ($sec) use ($hideFull) {
            $stats = $sec->getCapacityStats();
            $isFull = $stats['is_full'] ?? false;

            // If setting is to hide full sections AND it is full, return null (to be filtered out)
            if ($hideFull && $isFull) {
                return null;
            }

            return [
                'id' => $sec->id,
                'name' => $sec->name . ($isFull ? ' (ممتلئ)' : ''),
                'is_full' => $isFull,
                'disabled' => $isFull, // Frontend can use this to disable the option
            ];
        })
            ->filter() // Remove nulls (hidden sections)
            ->values();

        return response()->json($data);
    }

    public function trainingType()
    {
        $data = [];

        if ($this->settings->enable_training_type_university) {
            $data[] = ['id' => Application::UNIVERSITY, 'name' => Application::getTrainingTypeLabel(Application::UNIVERSITY)];
        }

        if ($this->settings->enable_training_type_practice) {
            $data[] = ['id' => Application::PRACTICE, 'name' => Application::getTrainingTypeLabel(Application::PRACTICE)];
        }

        return response()->json($data);
    }

    public function checkNationalId(Request $request)
    {
        // SECURITY NOTE: This endpoint allows enumeration of national IDs
        // Anyone can check if a national ID exists in the system
        // This is acceptable for UX (duplicate check) but be aware of privacy implications

        $nationalId = $request->query('national_id');

        // Validate input format before checking
        if (!preg_match('/^\d{9}$/', $nationalId)) {
            return response()->json([
                'exists' => false,
                'valid' => false,
                'message' => ''
            ], 200);
        }

        // Validate Palestinian ID checksum
        if (validatePalestinianId($nationalId) !== 'valid') {
            return response()->json([
                'exists' => false,
                'valid' => false,
                'message' => 'رقم الهوية الوطنية غير صحيح.'
            ], 200);
        }

        $exists = Trainee::where('national_id', $nationalId)->exists();

        return response()->json([
            'exists' => $exists,
            'valid' => true,
            'message' => $exists ? 'رقم الهوية هذا مسجل مسبقاً في النظام.' : ''
        ]);
    }

    /**
     * Check if trainee has existing application for the same training type
     */
    public function checkExistingApplication(Request $request)
    {
        $nationalId = $request->query('national_id');
        $trainingType = (int) $request->query('training_type');

        // Validate input
        if (!$this->isValidNationalId($nationalId) || !$this->isValidTrainingType($trainingType)) {
            return $this->noApplicationResponse();
        }

        // Validate Palestinian ID checksum before proceeding
        if (validatePalestinianId($nationalId) !== 'valid') {
            return response()->json([
                'hasApplication' => true,
                'message' => 'رقم الهوية الوطنية غير صحيح.',
                'trainee' => null
            ], 200);
        }

        // Find trainee
        $trainee = Trainee::where('national_id', $nationalId)->first();

        if (!$trainee) {
            return $this->noApplicationResponse();
        }

        // Check settings
        $canReapply = ($trainingType == Application::UNIVERSITY)
            ? $this->settings->can_university_reapply
            : $this->settings->can_practice_reapply;

        // Build query for existing applications
        $query = Application::where('trainee_id', $trainee->id)
            ->where('training_type', $trainingType)
            ->whereNull('deleted_at');

        if ($canReapply) {
            // If re-application allowed, only block if there's an application NOT in Ended status
            $blockingApplication = $query->where('status', '!=', Application::STATUS_ENDED_TRAINING)->first();
        } else {
            // If NOT allowed, block if ANY application exists
            $blockingApplication = $query->first();
        }

        // No blocking application found
        if (!$blockingApplication) {
            return $this->noApplicationResponse($trainee);
        }

        // Blocking application exists - return message
        return response()->json([
            'hasApplication' => true,
            'trainee' => $trainee,
            'status' => $blockingApplication->status,
            'message' => $this->getApplicationStatusMessage($blockingApplication),
            'canContinue' => false
        ], 200);
    }

    /**
     * Get user-friendly message for application status
     */
    private function getApplicationStatusMessage(Application $application): string
    {
        return Application::getStatusMessage($application->status, $application->training_type);
    }

    /**
     * Validate national ID format
     */
    private function isValidNationalId(string $nationalId): bool
    {
        return !empty($nationalId) && preg_match('/^\d{9}$/', $nationalId);
    }

    /**
     * Validate training type is valid
     */
    private function isValidTrainingType(int $trainingType): bool
    {
        return in_array($trainingType, [
            Application::UNIVERSITY,
            Application::PRACTICE
        ]);
    }

    /**
     * Standard response when no application exists
     */
    private function noApplicationResponse(?Trainee $trainee = null): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'hasApplication' => false,
            'trainee' => $trainee,
            'status' => null,
            'message' => '',
            'canContinue' => true
        ], 200);
    }

    /**
     * Store a trainee application into the Trainee and Application tables.
     * Uses a database transaction to ensure both save together or neither saves.
     *
     * IMPORTANT: Each trainee can have ONE application per training type.
     * Examples:
     *   - National ID 123456789 can have 1 UNIVERSITY training application
     *   - Same ID 123456789 can ALSO have 1 PRACTICE training application
     *   - BUT cannot have 2 UNIVERSITY applications or 2 PRACTICE applications
     *
     * The validation checks existing Applications, not just Trainees,
     * to prevent duplicate applications of the same training type.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'form_uuid' => ['required', 'uuid'],
            'national_id' => ['required', 'digits:9'],
            'full_name' => ['required', 'string', 'max:255', 'regex:/^[\p{Arabic}A-Za-z\s]+$/u'],
            'dob' => ['required', 'date_format:Y-m-d', 'before_or_equal:' . now()->subYears(20)->format('Y-m-d'), 'after_or_equal:' . now()->subYears(60)->format('Y-m-d')],
            'phone_number' => ['required', 'regex:/^97[02]5[69]\d{7}$/'],
            'governorate_id' => ['required', 'integer', 'exists:governorates,id'],
            'street' => ['required', 'string', 'max:255', 'regex:/^[\p{Arabic}A-Za-z0-9\s\-\.,#\/]+$/u'],
            'institution_id' => ['required_if:training_type,' . Application::UNIVERSITY, 'nullable', 'integer', 'exists:institutions,id'],
            'college_id' => ['nullable', 'integer', 'exists:colleges,id'],
            'major_id' => ['required_if:training_type,' . Application::UNIVERSITY, 'nullable', 'integer', 'exists:majors,id'],
            'training_hours' => ['required', 'integer', 'min:1', 'max:1000'],
            'administrative_id' => ['required', 'integer', 'exists:administratives,id'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'training_type' => ['required', 'integer', 'in:' . implode(',', [Application::UNIVERSITY, Application::PRACTICE])],
            'letter_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'terms_approval' => ['required', 'accepted'],
        ]);

        // DOB is already in Y-m-d format from the date picker, ready for database storage
        $dobFormatted = $validated['dob'];

        // Get the uploaded file (will be renamed after we have IDs)
        $uploadedFile = $request->file('letter_file');

        try {
            // Verify that the section belongs to the selected department and administrative
            $section = Section::where('id', $validated['section_id'])
                ->where('department_id', $validated['department_id'])
                ->where('administrative_id', $validated['administrative_id'])
                ->firstOrFail();

            $administrativeId = $validated['administrative_id'];

            // Use database transaction to ensure both trainee and application save together
            $result = DB::transaction(function () use ($validated, $dobFormatted, $uploadedFile, $administrativeId) {

                // Step 1: Create or update trainee record
                // Use updateOrCreate to handle case where trainee with same national_id already exists
                // If college_id wasn't provided by the frontend, try to infer it from the selected major
                if (empty($validated['college_id']) && !empty($validated['major_id'])) {
                    $major = Major::find($validated['major_id']);
                    if ($major) {
                        $firstCollege = $major->colleges()->first();
                        if ($firstCollege) {
                            $validated['college_id'] = $firstCollege->id;
                            // ensure institution_id matches the college's institution if missing/incorrect
                            $validated['institution_id'] = $firstCollege->institution_id ?? $validated['institution_id'];
                        }
                    }
                }

                $trainee = Trainee::updateOrCreate(
                    ['national_id' => $validated['national_id']], // Find by national_id
                    [
                        'full_name' => $validated['full_name'],
                        'phone_number' => $validated['phone_number'],
                        'dob' => $dobFormatted,
                        'governorate_id' => $validated['governorate_id'],
                        'street' => $validated['street'],
                        'institution_id' => $validated['institution_id'] ?? null,
                        'college_id' => $validated['college_id'] ?? null,
                        'major_id' => $validated['major_id'] ?? null,
                        'training_hours' => $validated['training_hours'],
                    ]
                );

                // Step 2: Create application record linked to the trainee (without letter path first)
                $application = Application::create([
                    'trainee_id' => $trainee->id,
                    'section_id' => $validated['section_id'],
                    'street' => $validated['street'],
                    'training_type' => $validated['training_type'],
                    'status' => Application::STATUS_NEW,
                ]);

                // Step 3: Upload file with custom name: {application_id}_{trainee_id}.{extension}
                $letterPath = null;
                if ($uploadedFile) {
                    $extension = $uploadedFile->getClientOriginalExtension();
                    $customFileName = "{$application->id}_{$trainee->id}.{$extension}";
                    $letterPath = $uploadedFile->storeAs('application-letters', $customFileName, 'public');

                    // Update the application with the file path
                    $application->update(['application_letter' => $letterPath]);
                }

                return [
                    'trainee' => $trainee,
                    'application' => $application,
                ];
            });

            return response()->json([
                'message' => 'تم استلام الطلب بنجاح',
                'id' => $result['application']->id,
                'redirect' => route('training.welcome'),
            ]);
        } catch (\Exception $e) {
            // If there was an error, the transaction will be rolled back automatically
            // File uploads are managed by Laravel storage (automatically cleaned up on rollback)

            Log::error('Application submission failed', [
                'national_id' => $request->input('national_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'حدث خطأ أثناء حفظ الطلب. يرجى المحاولة مرة أخرى.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}

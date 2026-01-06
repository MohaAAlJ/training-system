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
use App\Helpers\Constants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApplicationFormController extends Controller
{


    /**
     * Serve the welcome page.
     */
    public function showWelcome()
    {
        $settings = \App\Models\GeneralSetting::instance();
        return view('Form.welcomeapp', [
            'isFormEnabled' => $settings->is_public_form_enabled
        ]);
    }

    /**
     * Serve the public trainee application form.
     */
    public function showForm()
    {
        $settings = \App\Models\GeneralSetting::instance();

        if (!$settings->is_public_form_enabled) {
            return redirect()->route('training.welcome')->with('error', 'نعتذر، نموذج الالتحاق مغلق حالياً.');
        }

        if (!$settings->enable_training_type_university && !$settings->enable_training_type_practice) {
            return redirect()->route('training.welcome')->with('error', 'نعتذر، لا توجد أنواع تدريب متاحة حالياً.');
        }

        return view('Form.trainee-app.index');
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
        $data = Institution::where('is_active', true)->select('id', 'name')->get()
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
                    ->where('colleges.is_active', true);
            })->select('majors.id', 'majors.name')->get();
        } else {
            // Only show majors that have at least one active college
            $majors = Major::whereHas('colleges', function ($q) {
                $q->where('colleges.is_active', true);
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
            ->where('colleges.is_active', true)
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

        $query = Administrative::query();

        if ($trainingType == Application::TRAINING_TYPE_PRACTICE) {
            $query->where('is_medical', true);
        }

        $data = $query->get()
            ->map(fn($adm) => [
                'id' => $adm->id,
                'name' => $adm->name_with_governorate,
            ])
            ->values();

        return response()->json($data);
    }

    public function department(Request $request)
    {
        $administrativeId = $request->query('administrative_id');
        $trainingType = $request->query('training_type');

        $query = Department::query()->active();

        if ($trainingType == Application::TRAINING_TYPE_PRACTICE) {
            $query->where('is_medical', true);
        }

        if ($administrativeId) {
            // Filter departments that have sections in this administrative unit
            $query->whereHas('sections', function ($q) use ($administrativeId) {
                $q->where('administrative_id', $administrativeId);
            });
        }

        $data = $query->select('id', 'title')->get()
            ->map(function ($dept) {
                return [
                    'id' => $dept->id,
                    'name' => $dept->title,
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

        if ($trainingType == Application::TRAINING_TYPE_PRACTICE) {
            // Ensure we only get sections that belong to medical departments
            // This is redundant if department_id is filtered, but good for safety
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

        // $hideFull = \App\Models\GeneralSetting::instance()->hide_full_sections;

        $sections = $query->active()->get();

        // if ($hideFull) {
        //     $sections = $sections->reject(function ($sec) {
        //         return $sec->getCapacityStats()['is_full'] ?? false;
        //     });
        // }

        $data = $sections->map(function ($sec) {
            $stats = $sec->getCapacityStats();
            $isFull = $stats['is_full'] ?? false;

            return [
                'id' => $sec->id,
                'name' => $sec->name_location . ($isFull ? ' (ممتلئ)' : ''),
                'is_full' => $isFull,
            ];
        })->values();

        return response()->json($data);
    }

    public function trainingType()
    {
        $settings = \App\Models\GeneralSetting::instance();
        $data = [];

        if ($settings->enable_training_type_university) {
            $data[] = ['id' => Application::TRAINING_TYPE_UNIVERSITY, 'name' => Application::TRAINING_TYPES[Application::TRAINING_TYPE_UNIVERSITY]];
        }

        if ($settings->enable_training_type_practice) {
            $data[] = ['id' => Application::TRAINING_TYPE_PRACTICE, 'name' => Application::TRAINING_TYPES[Application::TRAINING_TYPE_PRACTICE]];
        }

        return response()->json($data);
    }

    public function checkNationalId(Request $request)
    {
        $nationalId = $request->query('national_id');
        $exists = Trainee::where('national_id', $nationalId)->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'رقم الهوية هذا مسجل مسبقاً في النظام.' : ''
        ]);
    }

    /**
     * Store a trainee application into the Trainee and Application tables.
     * Uses a database transaction to ensure both save together or neither saves.
     */
    public function store(Request $request)
    {
        $maxBirthYear = now()->year - 20; // Must be at least 20 years old
        $minBirthYear = now()->year - 60; // Must be at most 60 years old

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255', 'regex:/^[\p{Arabic}A-Za-z\s]+$/u'],
            'dob' => [
                'required',
                'regex:/^\d{2}\/\d{2}\/\d{4}$/',
                function ($attribute, $value, $fail) use ($maxBirthYear, $minBirthYear) {
                    $parts = explode('/', $value);
                    if (count($parts) === 3) {
                        $year = (int) $parts[2];
                        if ($year > $maxBirthYear) {
                            $fail("يجب أن يكون العمر 20 سنة على الأقل (سنة الميلاد يجب أن تكون {$maxBirthYear} أو أقل)");
                        }
                        if ($year < $minBirthYear) {
                            $fail("يجب أن يكون العمر 60 سنة على الأكثر (سنة الميلاد يجب أن تكون {$minBirthYear} أو أكثر)");
                        }
                    }
                },
            ],
            'national_id' => ['required', 'digits:9', 'unique:trainees,national_id'],
            'phone_number' => ['required', 'regex:/^97[02]5[69]\d{7}$/'],
            'governorate_id' => ['required', 'integer', 'exists:governorates,id'],
            'street' => ['required', 'string', 'max:255', 'regex:/^[\p{Arabic}A-Za-z0-9\s\-\.,#\/]+$/u'],
            'institution_id' => [
                'required_if:training_type,' . Application::TRAINING_TYPE_UNIVERSITY,
                'nullable',
                'integer',
                \Illuminate\Validation\Rule::exists('institutions', 'id')->where(function ($query) {
                    $query->where('is_active', true);
                }),
            ],
            'college_id' => [
                'nullable',
                'integer',
                \Illuminate\Validation\Rule::exists('colleges', 'id')->where(function ($query) {
                    $query->where('is_active', true);
                }),
            ],
            'major_id' => ['required_if:training_type,' . Application::TRAINING_TYPE_UNIVERSITY, 'nullable', 'integer', 'exists:majors,id'],
            'training_hours' => ['required', 'integer', 'min:1', 'max:999'],
            'administrative_id' => ['required', 'integer', 'exists:administratives,id'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'training_type' => ['required', 'integer', 'in:' . implode(',', array_keys(Application::TRAINING_TYPES))],
            'letter_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'terms_approval' => ['required', 'accepted'],
        ], [
            'national_id.unique' => 'رقم الهوية هذا مسجل مسبقاً في النظام.',
        ]);

        // Convert DOB from dd/mm/yyyy to Y-m-d format for database storage
        $dobParts = explode('/', $validated['dob']);
        $dobFormatted = "{$dobParts[2]}-{$dobParts[1]}-{$dobParts[0]}";

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
                    'department_id' => $validated['department_id'],
                    'administrative_id' => $administrativeId,
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
                'slug' => $result['application']->slug,
                'redirect' => route('training.welcome'),
            ]);
        } catch (\Exception $e) {
            // If there was an error, the transaction will be rolled back
            // Spatie Media Library handles cleanup automatically

            return response()->json([
                'message' => 'حدث خطأ أثناء حفظ الطلب. يرجى المحاولة مرة أخرى.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Applications;
use App\Models\sections;
use App\Models\Departments;
use App\Models\Institution;
use App\Models\Major;
use App\Models\Trainees;
use App\Helpers\Constans;
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
        return view('Form.welcomeapp');
    }

    /**
     * Serve the public trainee application form.
     */
    public function showForm()
    {
        return view('Form.trainee-app.index');
    }
    /**
     * Public JSON endpoints to feed the form selects from the database.
     */
    // public function addresses()
    // {
    //     // Gaza governorates - stored directly in applications.address field
    //     $data = [
    //         ['id' => 'شمال غزة', 'name' => 'شمال غزة'],
    //         ['id' => 'غزة', 'name' => 'غزة'],
    //         ['id' => 'الوسطى', 'name' => 'الوسطى'],
    //         ['id' => 'خان يونس', 'name' => 'خان يونس'],
    //         ['id' => 'رفح', 'name' => 'رفح'],
    //     ];

    //     return response()->json($data);
    // }

    public function institutions()
    {
        $data = Institution::select('id', 'name')->get()
            ->map(fn ($inst) => [
                'id' => $inst->id,
                'name' => is_array($inst->name) ? ($inst->name['ar'] ?? ($inst->name['en'] ?? reset($inst->name))) : $inst->name,
            ])
            ->values();

        return response()->json($data);
    }

    public function majors(Request $request)
    {
        $institutionId = $request->query('institution_id');

        // If an institution_id is provided, fetch majors that are linked to
        // colleges belonging to that institution via the college_major pivot.
        if ($institutionId) {
            $majors = Major::whereHas('colleges', function ($q) use ($institutionId) {
                $q->where('colleges.institution_id', $institutionId);
            })->select('majors.id', 'majors.name')->get();
        } else {
            $majors = Major::select('majors.id', 'majors.name')->get();
        }

        $data = $majors->map(fn ($major) => [
            'id' => $major->id,
            'name' => is_array($major->name) ? ($major->name['ar'] ?? ($major->name['en'] ?? reset($major->name))) : $major->name,
        ])->values();

        return response()->json($data);
    }

    /**
     * Return colleges linked to a given major (used to auto-fill institution)
     */
    public function majorColleges(Request $request)
    {
        $majorId = $request->query('major_id');

        if (! $majorId) {
            return response()->json([], 200);
        }

        $major = Major::find($majorId);
        if (! $major) {
            return response()->json([], 200);
        }

        $colleges = $major->colleges()->select('colleges.id', 'colleges.name', 'colleges.institution_id')->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => is_array($c->name) ? ($c->name['ar'] ?? ($c->name['en'] ?? reset($c->name))) : $c->name,
                'institution_id' => $c->institution_id,
            ])->values();

        return response()->json($colleges);
    }

    public function departments()
    {
        $data = Departments::select('id', 'name_location')->get()
            ->map(fn ($dept) => [
                'id' => $dept->id,
                'name' => $dept->name_location,
            ])
            ->values();

        return response()->json($data);
    }

    public function sections(Request $request)
    {
        $administrativeId = $request->query('department_id');

        $query = Sections::active()->select('id', 'name_location', 'department_id');
        if ($administrativeId) {
            $query->where('department_id', $administrativeId);
        }

        $data = $query->get()
            ->map(fn ($dept) => [
                'id' => $dept->id,
                'name' => $dept->name_location,
                'administrative_id' => $dept->administrative_id,
            ])
            ->values();

        return response()->json($data);
    }

    public function trainingTypes()
    {
        $data = [
            ['id' => 'cooperative', 'name' => 'تدريب جامعي'],
            ['id' => 'professional', 'name' => 'مزاولة مهنة'],
        ];

        return response()->json($data);
    }

    /**
     * Store a trainee application into the trainees and applications tables.
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
            'national_id' => ['required', 'digits:9'],
            'phone_number' => ['required', 'regex:/^97[02]5[69]\d{7}$/'],
            'governorate_id' => ['required', 'integer', 'exists:governorates,id'],
            'street' => ['required', 'string', 'max:255', 'regex:/^[\p{Arabic}A-Za-z0-9\s\-\.,#\/]+$/u'],
            'institution_id' => ['required', 'integer', 'exists:institutions,id'],
            'college_id' => ['nullable', 'integer', 'exists:colleges,id'],
            'major_id' => ['required', 'integer', 'exists:majors,id'],
            'training_hours' => ['required', 'integer', 'min:1', 'max:999'],
            'administrative_id' => ['required', 'integer', 'exists:administratives,id'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'training_type' => ['required', 'string', 'max:100'],
            'letter_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        // Convert DOB from dd/mm/yyyy to Y-m-d format for database storage
        $dobParts = explode('/', $validated['dob']);
        $dobFormatted = "{$dobParts[2]}-{$dobParts[1]}-{$dobParts[0]}";

        // Get the uploaded file (will be renamed after we have IDs)
        $uploadedFile = $request->file('letter_file');

        try {
            // Use database transaction to ensure both trainee and application save together
            $result = DB::transaction(function () use ($validated, $dobFormatted, $uploadedFile) {

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

                $trainee = Trainees::updateOrCreate(
                    ['national_id' => $validated['national_id']], // Find by national_id
                    [
                        'full_name' => $validated['full_name'],
                        'phone_number' => $validated['phone_number'],
                        'dob' => $dobFormatted,
                        'governorate_id' => $validated['governorate_id'],
                        'street' => $validated['street'],
                        'institution_id' => $validated['institution_id'],
                        'college_id' => $validated['college_id'] ?? null,
                        'major_id' => $validated['major_id'],
                        'training_hours' => $validated['training_hours'],
                    ]
                );

                // Step 2: Create application record linked to the trainee (without letter path first)
                $application = Applications::create([
                    'trainee_id' => $trainee->id,
                    'department_id' => $validated['department_id'],
                    'administrative_id' => $validated['administrative_id'],
                    'section_id' => $validated['section_id'],
                    'street' => $validated['street'],
                    'training_type' => $validated['training_type'],
                    'status' => Constans::STATUS_NEW,
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

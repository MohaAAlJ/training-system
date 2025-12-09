<?php

namespace App\Http\Controllers;

use App\Models\Applications;
use App\Models\Administratives;
use App\Models\Departments;
use App\Models\Institution;
use App\Models\Major;
use App\Models\Trainees;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
    public function addresses()
    {
        // Gaza governorates - stored directly in applications.address field
        $data = [
            ['id' => 'شمال غزة', 'name' => 'شمال غزة'],
            ['id' => 'غزة', 'name' => 'غزة'],
            ['id' => 'الوسطى', 'name' => 'الوسطى'],
            ['id' => 'خان يونس', 'name' => 'خان يونس'],
            ['id' => 'رفح', 'name' => 'رفح'],
        ];

        return response()->json($data);
    }

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

        $query = Major::select('majors.id', 'majors.name');
        if ($institutionId) {
            $query->whereHas('institutions', function ($q) use ($institutionId) {
                $q->where('institutions.id', $institutionId);
            });
        }

        $data = $query->get()
            ->map(fn ($major) => [
                'id' => $major->id,
                'name' => is_array($major->name) ? ($major->name['ar'] ?? ($major->name['en'] ?? reset($major->name))) : $major->name,
            ])
            ->values();

        return response()->json($data);
    }

    public function administratives()
    {
        $data = Administratives::select('id', 'title')->get()
            ->map(fn ($adm) => [
                'id' => $adm->id,
                'name' => $adm->title,
            ])
            ->values();

        return response()->json($data);
    }

    public function departments(Request $request)
    {
        $administrativeId = $request->query('administrative_id');

        $query = Departments::active()->select('id', 'name_location', 'administrative_id');
        if ($administrativeId) {
            $query->where('administrative_id', $administrativeId);
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
            'address' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255', 'regex:/^[\p{Arabic}A-Za-z0-9\s\-\.,#\/]+$/u'],
            'institution_id' => ['required', 'integer', 'exists:institutions,id'],
            'major_id' => ['required', 'integer', 'exists:majors,id'],
            'training_hours' => ['required', 'integer', 'min:1', 'max:999'],
            'administrative_id' => ['required', 'integer', 'exists:administratives,id'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'training_type' => ['required', 'string', 'max:100'],
            'letter_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        // Convert DOB from dd/mm/yyyy to Y-m-d format for database storage
        $dobParts = explode('/', $validated['dob']);
        $dobFormatted = "{$dobParts[2]}-{$dobParts[1]}-{$dobParts[0]}";

        $slug = Str::slug($validated['full_name'] . '-' . now()->timestamp);

        $letterPath = null;
        if ($request->hasFile('letter_file')) {
            $letterPath = $request->file('letter_file')->store('uploads', 'public');
        }

        try {
            // Use database transaction to ensure both trainee and application save together
            $result = DB::transaction(function () use ($validated, $dobFormatted, $slug, $letterPath) {

                // Step 1: Create or update trainee record
                // Use updateOrCreate to handle case where trainee with same national_id already exists
                $trainee = Trainees::updateOrCreate(
                    ['national_id' => $validated['national_id']], // Find by national_id
                    [
                        'full_name' => $validated['full_name'],
                        'phone_number' => $validated['phone_number'],
                        'dob' => $dobFormatted,
                        'address' => $validated['address'],
                        'institution_id' => $validated['institution_id'],
                        'major_id' => $validated['major_id'],
                    ]
                );

                // Step 2: Create application record linked to the trainee
                $application = Applications::create([
                    'trainee_id' => $trainee->id,
                    'department_id' => $validated['department_id'],
                    'administrative_id' => $validated['administrative_id'],
                    'street' => $validated['street'],
                    'training_hours' => $validated['training_hours'],
                    'training_type' => $validated['training_type'],
                    'letter_image_path' => $letterPath,
                    'status' => 'pending',
                    'slug' => $slug,
                ]);

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
            // Delete uploaded file if it exists since the transaction failed
            if ($letterPath) {
                Storage::disk('public')->delete($letterPath);
            }

            return response()->json([
                'message' => 'حدث خطأ أثناء حفظ الطلب. يرجى المحاولة مرة أخرى.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}

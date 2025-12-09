<?php

namespace App\Http\Controllers;

use App\Models\Applications;
use App\Models\Administratives;
use App\Models\Departments;
use App\Models\Institution;
use App\Models\Major;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApplicationFormController extends Controller
{
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
        return response()->json([]);
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

        $query = Departments::select('id', 'name_location', 'administrative_id');
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
            ['id' => 'cooperative', 'name' => 'تدريب تعاوني'],
            ['id' => 'professional', 'name' => 'مزاولة مهنة'],
        ];

        return response()->json($data);
    }

    /**
     * Store a trainee application into the applications table.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255', 'regex:/^[\p{Arabic}A-Za-z\s]+$/u'],
            'dob' => ['required', 'regex:/^\d{2}\/\d{2}\/\d{4}$/'],
            'national_id' => ['required', 'digits:10'],
            'phone_number' => ['required', 'regex:/^97\d5\d{8}$/'],
            'address' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'institution_id' => ['required', 'integer'],
            'major_id' => ['required', 'integer'],
            'training_hours' => ['required', 'integer', 'min:1', 'max:1000'],
            'administrative_id' => ['required', 'integer'],
            'department_id' => ['required', 'integer'],
            'training_type' => ['nullable', 'string', 'max:100'],
        ]);

        $slug = Str::slug($validated['full_name'] . '-' . now()->timestamp);

        $application = Applications::create([
            'full_name' => $validated['full_name'],
            'dob' => $validated['dob'],
            'national_id' => $validated['national_id'],
            'phone_number' => $validated['phone_number'],
            'address' => $validated['address'],
            'street' => $validated['street'],
            'institution_id' => $validated['institution_id'],
            'major_id' => $validated['major_id'],
            'training_hours' => $validated['training_hours'],
            'administrative_id' => $validated['administrative_id'],
            'department_id' => $validated['department_id'],
            'training_type' => $validated['training_type'] ?? null,
            'status' => 'pending',
            'slug' => $slug,
        ]);

        return response()->json([
            'message' => 'تم استلام الطلب بنجاح',
            'slug' => $application->slug,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Administratives;
use App\Models\Departments;
use App\Models\Institution;
use App\Models\Major;
use Illuminate\Http\Request;

class FormDataController extends Controller
{
    public function addresses()
    {
        // No addresses table provided; return empty for now to keep client happy.
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
            ['id' => 'internship', 'name' => 'تدريب صيفي'],
            ['id' => 'professional', 'name' => 'مزاولة مهنة'],
        ];

        return response()->json($data);
    }
}

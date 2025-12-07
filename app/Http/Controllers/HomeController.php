<?php

namespace App\Http\Controllers;

use App\Models\Trainees;
use App\Models\Applications;
use App\Models\Departments;
use App\Models\Institution;
use App\Models\Major;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $counts = [
            'institutions' => Institution::count(),
            'majors' => Major::count(),
            'departments' => Departments::count(),
            'applications_pending' => Applications::where('status', 'pending')->count(),
        ];

        $recentTrainees = Trainees::with(['institution', 'major'])->latest()->limit(6)->get();

        $latestApplications = Applications::with(['trainee', 'department'])->latest()->limit(6)->get();

        $topMajors = Major::withCount('trainees')->orderByDesc('trainees_count')->limit(5)->get();

            return view('dashboard.home', compact('counts', 'recentTrainees', 'latestApplications', 'topMajors'));
    }
}

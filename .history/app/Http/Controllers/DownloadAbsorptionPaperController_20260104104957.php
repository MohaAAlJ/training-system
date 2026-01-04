<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class DownloadAbsorptionPaperController extends Controller
{
    public function download(Application $application)
    {
        $application->load(['trainee.major', 'trainee.governorate', 'department']);

        $pdf = Pdf::loadView('pdf.absorption_paper', compact('application'));

        return $pdf->download('absorption_paper_' . $application->id . '.pdf');
    }
}

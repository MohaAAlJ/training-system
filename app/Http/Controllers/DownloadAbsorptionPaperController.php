<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as Pdf;

use Illuminate\Http\Request;

class DownloadAbsorptionPaperController extends Controller
{
    public function __invoke(Request $request, Application $application)
    {
        $application->load(['department', 'section']);

        $data = [
            'application' => $application,
            'trainee'    => $application->trainee,
            'date'       => date('Y-m'),
        ];

        $pdf = Pdf::loadView('pdf.absorption_paper', $data, [], [
            'format' => 'A4',
            'orientation' => 'P'
        ]);

        $filename = date('Y-m-d') . '_' . $application->trainee->national_id . '.pdf';

        // Save to public storage
        $path = 'application-absorption-papers/' . $filename;
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $pdf->output());

        if ($request->query('mode') === 'download') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}

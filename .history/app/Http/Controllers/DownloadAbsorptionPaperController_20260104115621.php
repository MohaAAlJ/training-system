<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as Pdf;
use Illuminate\Http\Request;

class DownloadAbsorptionPaperController extends Controller
{
    public function download(Request $request, Application $application)
    {
        dd($request->all());
        \Illuminate\Support\Facades\Log::info('DownloadAbsorptionPaperController hit', ['mode' => $request->input('mode'), 'all' => $request->all()]);
        $application->load(['trainee.major', 'trainee.governorate', 'department']);

        $pdf = Pdf::loadView('pdf.absorption_paper', compact('application'));

        if ($request->input('mode') === 'stream') {
            return $pdf->stream('absorption_paper_' . $application->id . '.pdf');
        }

        return $pdf->download('absorption_paper_' . $application->id . '.pdf');
    }
}

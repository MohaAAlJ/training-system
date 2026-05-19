<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as Pdf;
use Illuminate\Http\Request;

class DownloadAbsorptionPaperController extends Controller
{
    public function __invoke(Request $request, Application $application)
    {
        $application->load(['section.departments' => fn($q) => $q->visible()]);

        $data = [
            'application' => $application,
            'trainee'    => $application->trainee,
            'date'       => date('Y-m'),
        ];

        $pdf = Pdf::loadView('pdf.absorption_paper', $data, [], [
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 0,
            'margin_footer' => 5,
            'default_font' => 'notonaskh',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'autoVietnamese' => false,
            'autoArabic' => true,
        ]);

        $filename = date('Y-m-d') . '_' . $application->trainee->national_id . '.pdf';

        // Ensure directory exists
        $directory = 'application-absorption-papers';
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($directory)) {
            \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($directory);
        }

        // Save to public storage
        $path = $directory . '/' . $filename;
        $saved = \Illuminate\Support\Facades\Storage::disk('public')->put($path, $pdf->output());

        if ($saved) {
            \Illuminate\Support\Facades\Log::info("Absorption paper saved successfully to: " . $path);
        } else {
            \Illuminate\Support\Facades\Log::error("Failed to save absorption paper to: " . $path);
        }

        if ($request->query('mode') === 'download') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as Pdf;

class DownloadAbsorptionPaperController extends Controller
{
    public function __invoke(Application $application)
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
        return $pdf->stream('ورقة_استيعاب_' . $application->trainee->national_id . '_' . date('Y-m') . '.pdf');
    }
}

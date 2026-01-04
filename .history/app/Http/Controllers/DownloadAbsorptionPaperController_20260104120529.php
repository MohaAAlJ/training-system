<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as Pdf;
use Illuminate\Http\Request;

class DownloadAbsorptionPaperController extends Controller
{
    public function __invoke(Application $application)
    {
        $application->load(['trainee.major', 'trainee.institution', 'trainee.college', 'department', 'section', 'administrative.user']);

        $adminName = $application->administrative->user->name ?? 'المدير الإداري';

        $data = [
            'app'        => $application,
            'trainee'    => $application->trainee,
            'admin_name' => $adminName,
            'date'       => date('Y-m-d'),
        ];

        $pdf = Pdf::loadView('pdf.absorption_paper', $data, [], [
            'format' => 'A4',
            'orientation' => 'P'
        ]);
        return $pdf->stream('ورقة_استيعاب_' . $application->trainee->national_id . '.pdf');
    }
}

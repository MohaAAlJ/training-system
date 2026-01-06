<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as Pdf;
use Illuminate\Http\Request;

class DownloadAbsorptionPaperController extends Controller
{
    public function __invoke(Application $application)
    {
        $user = auth()->user();

        // 1. Role check: Only MOH can download
        if (!$user->isMinistry()) {
            abort(403, 'Unauthorized. Only Ministry of Health users can download this paper.');
        }

        // 2. Status check: Status must be 2 (Initial Approval), 5 (Started Training), or 6 (Ended Training)
        if (!in_array((int)$application->status, [
            Application::STATUS_INITIAL_APPROVE,
            Application::STATUS_STARTED_TRAINING,
            Application::STATUS_ENDED_TRAINING,
        ])) {
            abort(403, 'Unauthorized. Application status must be Initial Approval, Started Training, or Ended Training.');
        }

        // 3. Training Type check: Only PRACTICE training types
        if ($application->training_type !== Application::TRAINING_TYPE_PRACTICE) {
            abort(403, 'Unauthorized. Only Practice training types are allowed for this download.');
        }

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

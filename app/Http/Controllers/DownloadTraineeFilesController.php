<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\College;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DownloadTraineeFilesController extends Controller
{
    public function __invoke(Request $request, Application $application)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            if (!$user->isCollegeSupervisor()) {
                abort(403);
            }

            $college = College::where('user_id', $user->id)->first();
            if (!$college || $application->college_id !== $college->id) {
                abort(403);
            }
        }

        if ($application->training_type !== Application::UNIVERSITY || $application->status !== Application::STATUS_ENDED_TRAINING) {
            abort(404);
        }

        $media = $application->getFirstMedia('trainee_application_files');
        if (!$media) {
            abort(404, 'لم يتم رفع الملفات بعد');
        }

        return response()->download($media->getPath(), $media->file_name);
    }
}

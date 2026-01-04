<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as Pdf;
use Illuminate\Http\Request;

class DownloadAbsorptionPaperController extends Controller
{
    public function __invoke(Application $application)
    {
        // 1. تحميل العلاقات لضمان عدم حدوث استعلامات كثيرة داخل الـ View
        $application->load(['trainee.major', 'trainee.institution', 'trainee.college', 'department', 'section', 'administrative.user']);

        // 2. تجهيز البيانات التي يحتاجها ملف Blade
        // (تأكدنا من جلب اسم المدير الإداري ليعرض في الترويسة)
        $adminName = $application->administrative->user->name ?? 'المدير الإداري';

        $data = [
            'app'        => $application, // الـ Blade يستخدم المتغير $app
            'trainee'    => $application->trainee,
            'admin_name' => $adminName,
            'date'       => date('Y-m-d'),
        ];

        // 3. إعداد ملف PDF
        $pdf = Pdf::loadView('pdf.absorption_paper', $data, [], [
            'format' => 'A4',
            'orientation' => 'P'
        ]);

        // 4. استخدام stream() للعرض داخل المتصفح (Preview)
        // هذا يسمح للمستخدم برؤية الملف ثم الضغط على زر الطباعة الموجود في المتصفح
        return $pdf->stream('ورقة_استيعاب_' . $application->trainee->national_id . '.pdf');
    }
}

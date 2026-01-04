<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلب التحاق تدريب</title>
    <style>
        @page {
            margin: 10mm 15mm;
            header: page-header;
            footer: page-footer;
        }

        body {
            font-family: 'xbriyaz', 'sans-serif';
            direction: rtl;
            text-align: right;
            font-size: 12pt;
            line-height: 1.6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 5px;
            vertical-align: middle;
        }

        .header-table td {
            vertical-align: top;
        }

        .header-right {
            text-align: right;
            font-size: 11pt;
            width: 40%;
        }

        .header-center {
            text-align: center;
            width: 20%;
        }

        .header-left {
            text-align: left;
            font-size: 10pt;
            width: 40%;
            direction: ltr;
        }

        .logo {
            width: 80px;
            height: auto;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            margin: 20px 0;
            text-decoration: underline;
        }

        .admin-section {
            border: 2px solid #000;
            padding: 15px;
            margin-top: 10px;
        }

        .admin-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
            font-size: 13pt;
        }

        .field-row {
            margin-bottom: 15px;
        }

        .field-label {
            font-weight: bold;
            margin-left: 5px;
        }

        .field-value {
            border-bottom: 1px dashed #444;
            display: inline-block;
            min-width: 150px;
            text-align: center;
            padding: 0 10px;
            font-weight: bold;
        }
        
        .field-value-long {
             min-width: 300px;
        }

        .checkbox-group {
            text-align: center;
            margin: 30px 0;
        }

        .checkbox-item {
            display: inline-block;
            margin: 0 40px;
        }

        .checkbox-box {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 1px solid #000;
            margin-left: 8px;
            vertical-align: middle;
        }

        .signature-table {
            margin-top: 50px;
            width: 100%;
        }

        .footer {
            text-align: center;
            font-size: 9pt;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <htmlpageheader name="page-header">
        <table class="header-table">
            <tr>
                <td class="header-right">
                    كلية تنمية القدرات الجامعية<br>
                    جمعية الهلال الأحمر الفلسطيني<br>
                    فلسطين – خان يونس
                </td>
                <td class="header-center">
                    <img src="{{ public_path('images/college_logo.png') }}" class="logo">
                </td>
                <td class="header-left">
                    University College of Ability Development<br>
                    Palestine Red Crescent Society<br>
                    Palestine - Khan Younis
                </td>
            </tr>
        </table>
        <div style="border-bottom: 3px double #000; margin-top: 10px;"></div>
    </htmlpageheader>

    <div class="title">
        طلب التحاق تدريب امتياز في مستشفى/مراكز جمعية الهلال الأحمر الفلسطيني
    </div>

    <div class="admin-section">
        <div class="admin-title">لإستخدام الإدارة</div>

        <div style="margin-bottom: 15px;">
            <strong>السيد/ة عميد كلية تنمية القدرات الجامعية المحترم</strong><br>
            <strong>مسؤول ملف التدريب وتعليم الطوارئ المحترم</strong>
        </div>

        <div style="margin-bottom: 20px;">
            تحية طيبة وبعد،،،
        </div>

        <div class="field-row">
            <span class="field-label">أنا الطالب/ة:</span>
            <span class="field-value field-value-long">{{ $application->trainee->full_name ?? '' }}</span>
            
            <span class="field-label" style="margin-right: 20px;">التخصص:</span>
            <span class="field-value">{{ $application->trainee->major->name ?? '' }}</span>
        </div>

        <div class="field-row">
            <span class="field-label">أرجو التكرم بالموافقة على طلبي بالتدريب في قسم:</span>
            <span class="field-value field-value-long">{{ $application->department->title ?? '' }}</span>
        </div>

        <table style="margin-bottom: 15px;">
            <tr>
                <td width="50%">
                    <span class="field-label">عنوان السكن:</span>
                    <span class="field-value" style="width: 250px;">
                        {{ $application->trainee->governorate->name ?? '' }} - {{ $application->trainee->street ?? '' }}
                    </span>
                </td>
                <td width="50%">
                    <span class="field-label">رقم الجوال:</span>
                    <span class="field-value">{{ $application->trainee->phone_number ?? '' }}</span>
                </td>
            </tr>
        </table>
        
        <div class="field-row">
             <span class="field-label">مدة التدريب:</span>
             <span class="field-value">{{ $application->duration ?? '' }} شهر/أشهر</span>
             
             <span class="field-label" style="margin-right: 30px;">تاريخ البدء المقترح:</span>
             <span class="field-value">{{ $application->start_date?->format('Y-m-d') ?? '' }}</span>
        </div>

        <div class="checkbox-group">
            <div class="checkbox-item">
                <span class="checkbox-box"></span>
                <strong>مع الموافقة</strong>
            </div>
            <div class="checkbox-item">
                <span class="checkbox-box"></span>
                <strong>عدم الموافقة</strong>
            </div>
        </div>

        <div style="margin-top: 15px; line-height: 2;">
            نحيطكم علما بانه لا مانع لدينا نحو استيعاب المتدرب المذكور اعلاه ضمن برنامج التدريب، وذلك ضمن المعايير التالية:
            <ul style="margin-top: 5px; margin-right: 20px;">
                <li>الإلتزام بتدريب المتدرب تحت إشراف المشرف المكلف من طرفكم.</li>
                <li>الإلتزام بمتابعة دوام المتدرب.</li>
            </ul>
        </div>

        <table class="signature-table">
            <tr>
                <td style="text-align: center;">
                    <strong>التوقيع:</strong> .....................................
                </td>
                <td style="text-align: center;">
                    <strong>الختم:</strong> .....................................
                </td>
            </tr>
        </table>
    </div>

    <htmlpagefooter name="page-footer">
        <div class="footer">
            جمعية الهلال الأحمر الفلسطيني - مدينة الأمل - خانيونس
        </div>
    </htmlpagefooter>

</body>
</html>
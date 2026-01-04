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
            line-height: 1.8;
            color: #1a1a1a;
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
            padding: 8px;
        }

        .header-right {
            text-align: right;
            font-size: 11pt;
            width: 40%;
            line-height: 1.7;
            font-weight: 500;
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
            line-height: 1.7;
            font-weight: 500;
        }

        .logo {
            width: 85px;
            height: auto;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 15pt;
            margin: 25px 0 20px 0;
            text-decoration: underline;
            text-decoration-thickness: 2px;
            text-underline-offset: 5px;
            color: #2c3e50;
            letter-spacing: 0.3px;
        }

        .admin-section {
            border: 2.5px solid #2c3e50;
            padding: 25px;
            margin-top: 15px;
            background: #fafafa;
            border-radius: 2px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .admin-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 25px;
            font-size: 14pt;
            color: #2c3e50;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
            letter-spacing: 0.5px;
        }

        .field-row {
            margin-bottom: 18px;
            line-height: 2.2;
        }

        .field-label {
            font-weight: 600;
            margin-left: 8px;
            color: #2c3e50;
        }

        .field-value {
            border-bottom: 1.5px dotted #555;
            display: inline-block;
            min-width: 150px;
            text-align: center;
            padding: 2px 12px;
            font-weight: 600;
            color: #1a1a1a;
            background: linear-gradient(to bottom, transparent 90%, #f0f0f0 90%);
        }
        
        .field-value-long {
             min-width: 300px;
        }

        .checkbox-group {
            text-align: center;
            margin: 35px 0;
            padding: 20px;
            background: #ffffff;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        .checkbox-item {
            display: inline-block;
            margin: 0 50px;
            font-size: 13pt;
        }

        .checkbox-box {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid #2c3e50;
            margin-left: 10px;
            vertical-align: middle;
            border-radius: 3px;
            background: #ffffff;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
        }

        .signature-table {
            margin-top: 55px;
            width: 100%;
            background: #ffffff;
            padding: 15px;
            border-radius: 4px;
        }

        .signature-table td {
            padding: 10px;
        }

        .footer {
            text-align: center;
            font-size: 9.5pt;
            border-top: 2px solid #2c3e50;
            padding-top: 8px;
            margin-top: 20px;
            color: #555;
            font-weight: 500;
        }

        ul {
            margin-top: 10px;
            margin-right: 25px;
            line-height: 2.2;
        }

        ul li {
            margin-bottom: 8px;
            color: #2c3e50;
        }

        strong {
            color: #2c3e50;
        }

        .intro-text {
            margin-bottom: 25px;
            padding: 15px;
            background: #ffffff;
            border-left: 4px solid #2c3e50;
            border-radius: 2px;
        }

        .notice-text {
            margin-top: 20px;
            line-height: 2.2;
            padding: 18px;
            background: #ffffff;
            border-radius: 4px;
            border: 1px solid #e0e0e0;
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
        <div style="border-bottom: 3px double #2c3e50; margin-top: 12px;"></div>
    </htmlpageheader>

    <div class="admin-section">
        <div class="title">
            طلب التحاق تدريب امتياز في مستشفى/مراكز جمعية الهلال الأحمر الفلسطيني
        </div>

        <div class="admin-title">لإستخدام الإدارة</div>

        <div class="intro-text">
            <strong>السيد/ة عميد كلية تنمية القدرات الجامعية المحترم</strong><br>
            <strong>مسؤول ملف التدريب وتعليم الطوارئ المحترم</strong>
        </div>

        <div style="margin-bottom: 25px; font-weight: 500;">
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

        <table style="margin-bottom: 18px;">
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

        <div class="notice-text">
            نحيطكم علما بانه لا مانع لدينا نحو استيعاب المتدرب المذكور اعلاه ضمن برنامج التدريب، وذلك ضمن المعايير التالية:
            <ul>
                <li>الإلتزام بتدريب المتدرب تحت إشراف المشرف المكلف من طرفكم.</li>
                <li>الإلتزام بمتابعة دوام المتدرب.</li>
            </ul>
        </div>

        <table class="signature-table">
            <tr>
                <td style="text-align: center;">
                    <strong style="font-size: 13pt;">التوقيع:</strong> <span style="border-bottom: 1.5px solid #555; display: inline-block; min-width: 200px; margin-right: 10px;"></span>
                </td>
                <td style="text-align: center;">
                    <strong style="font-size: 13pt;">الختم:</strong> <span style="border-bottom: 1.5px solid #555; display: inline-block; min-width: 200px; margin-right: 10px;"></span>
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
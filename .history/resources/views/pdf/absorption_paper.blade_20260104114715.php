<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلب التحاق تدريب</title>
    <style>
        @page {
            margin: 8mm 12mm;
            header: page-header;
            footer: page-footer;
        }

        body {
            font-family: 'xbriyaz', 'sans-serif';
            direction: rtl;
            text-align: right;
            font-size: 11.5pt;
            line-height: 1.6;
            color: #000;
        }

        .page-container {
            border: 4px solid #000;
            padding: 0;
            min-height: 260mm;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 5px;
            vertical-align: middle;
        }

        .header-section {
            border-bottom: 2px solid #000;
            padding: 12px 20px;
        }

        .header-table td {
            vertical-align: top;
            padding: 5px;
        }

        .header-right {
            text-align: right;
            font-size: 10.5pt;
            width: 40%;
            line-height: 1.5;
            font-weight: 600;
            color: #000080;
        }

        .header-center {
            text-align: center;
            width: 20%;
        }

        .header-left {
            text-align: left;
            font-size: 9.5pt;
            width: 40%;
            direction: ltr;
            line-height: 1.5;
            font-weight: 600;
            color: #000080;
        }

        .logo {
            width: 200px;
            height: auto;
        }

        .main-title {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            margin: 0;
            padding: 12px 20px;
            border-bottom: 2px solid #000;
            background: #f5f5f5;
        }

        .content-area {
            padding: 20px 25px;
        }

        .section-title {
            text-align: center;
            font-weight: bold;
            font-size: 12.5pt;
            margin: 20px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid #ccc;
        }

        .addressee-box {
            border-right: 4px solid #000;
            padding-right: 15px;
            margin: 20px 0;
            min-height: 80px;
        }

        .greeting {
            margin: 15px 0;
            font-weight: bold;
        }

        .field-row {
            margin-bottom: 12px;
            line-height: 2;
        }

        .field-label {
            font-weight: 600;
            margin-left: 5px;
        }

        .field-value {
            border-bottom: 1px dotted #000;
            display: inline-block;
            min-width: 120px;
            text-align: center;
            padding: 0 8px;
            font-weight: 600;
        }
        
        .field-value-long {
             min-width: 250px;
        }

        .approval-section {
            text-align: center;
            margin: 25px 0;
            padding: 15px;
            background: #fafafa;
            border: 1px solid #ddd;
        }

        .approval-option {
            display: inline-block;
            margin: 0 40px;
            font-size: 12pt;
        }

        .checkbox {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #000;
            margin-left: 8px;
            vertical-align: middle;
        }

        .notice-box {
            margin-top: 20px;
            padding: 15px;
            background: #fafafa;
            border: 1px solid #ddd;
            line-height: 1.8;
        }

        .notice-box ul {
            margin: 10px 0 0 25px;
            padding: 0;
        }

        .notice-box li {
            margin-bottom: 8px;
        }

        .signature-area {
            margin-top: 40px;
            padding-top: 20px;
        }

        .signature-table td {
            text-align: center;
            padding: 15px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 180px;
            margin-right: 10px;
        }

        .footer-section {
            text-align: center;
            font-size: 9pt;
            padding: 8px 20px;
            border-top: 2px solid #000;
            margin-top: auto;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="page-container">
        <!-- Header -->
        <div class="header-section">
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
                        Palestine – Khan Younis
                    </td>
                </tr>
            </table>
        </div>

        <!-- Main Title -->
        <div class="main-title">
            طلب التحاق تدريب امتياز في مستشفيات/مراكز جمعية الهلال الأحمر الفلسطيني
        </div>

        <!-- Content -->
        <div class="content-area">
            <div class="section-title">لإستخدام الإدارة</div>

            <div class="addressee-box">
                السيد/ة عميد كلية تنمية القدرات الجامعية المحترم<br>
                مسؤول ملف التدريب وتعليم الطوارئ المحترم
            </div>

            <div class="greeting">
                تحية طيبة وبعد،،،
            </div>

            <div class="field-row">
                <span class="field-label">أنا الطالب/ة: </span>
                <span class="field-value field-value-long">{{ $application->trainee->full_name ?? '' }}</span>
                
                <span class="field-label">التخصص: </span>
                <span class="field-value">{{ $application->trainee->major->name ?? '' }}</span>
            </div>

            <div class="field-row">
                <span class="field-label">أرجو التكرم بالموافقة على طلبي بالتدريب في قسم: </span>
                <span class="field-value field-value-long">{{ $application->section->name_location ?? '' }}</span>
            </div>

            <table style="margin: 15px 0;">
                <tr>
                    <td width="50%">
                        <span class="field-label">عنوان السكن: </span>
                        <span class="field-value" style="min-width: 180px;">
                            {{ $application->trainee->governorate?->name ?? '' }} - {{ $application->trainee->street ?? '' }}
                        </span>
                    </td>
                    <td width="50%">
                        <span class="field-label">رقم الجوال: </span>
                        <span class="field-value">{{ $application->trainee->phone_number ?? '' }}</span>
                    </td>
                </tr>
            </table>
            
            <div class="field-row">
                 <span class="field-label">عدد ساعات التدريب: </span>
                 <span class="field-value">{{ $application->trainee->training_hours ?? '' }} ساعة</span>
                 
                 <span class="field-label">   تاريخ البدء المقترح: </span>
                 <span class="field-value">{{ $application->start_date?->format('Y-m-d') ?? '' }}</span>
            </div>

            <div class="approval-section">
                <div class="approval-option">
                    <span class="checkbox"></span>
                    <strong>مع الموافقة</strong>
                </div>
            </div>

            <div class="notice-box">
                نحيطكم علما بانه لا مانع لدينا نحو استيعاب المتدرب المذكور اعلاه ضمن برنامج التدريب في  {{ $application->section->name_location ?? '' }}, وذلك ضمن المعايير التالية:
                <ul>
                    <li>الإلتزام بتدريب المتدرب تحت إشراف المشرف المكلف من طرفكم.</li>
                    <li>الإلتزام بمتابعة دوام المتدرب.</li>
                </ul>
            </div>

            <div class="signature-area">
                <table class="signature-table">
                    <tr>
                        <td>
                            <strong>التوقيع:</strong>
                            <span class="signature-line"></span>
                        </td>
                        <td>
                            <strong>الختم:</strong>
                            <span class="signature-line"></span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-section">
            جمعية الهلال الأحمر الفلسطيني - مدينة الأمل - خانيونس
        </div>
    </div>

</body>
</html>
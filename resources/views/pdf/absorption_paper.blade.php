<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>طلب التحاق تدريب</title>
    <style>
        @page {
            margin-top: 8mm;
            margin-bottom: 35mm;
            margin-left: 8mm;
            margin-right: 8mm;
            footer: html_myFooter;
        }

        .page-border {
            position: fixed;
            top: -3mm;
            bottom: -30mm;
            left: -3mm;
            right: -3mm;
            border: 2pt solid #000;
            z-index: -1;
        }

        body {
            font-family: 'notonaskh', sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 13pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .header-section {
            width: 100%;
            text-align: center;
            margin-bottom: 10px;
        }

        .header-section img {
            width: 100%;
            max-width: 100%;
        }

        .main-title {
            text-align: center;
            font-weight: bold;
            font-size: 16pt;
            padding: 8px 15px;
            border-top: 2.2pt solid #000;
            border-bottom: 2.2pt solid #000;
            background: #f5f5f5;
            margin-bottom: 12px;
        }

        .content-area {
            padding: 0;
        }

        .section-title {
            text-align: center;
            font-weight: bold;
            font-size: 15pt;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 1.2pt solid #ccc;
        }

        .addressee-box {
            border-right: 6px solid #000;
            padding-right: 20px;
            margin: 10px 0;
            line-height: 1.5;
            font-size: 13pt;
        }

        .greeting {
            margin: 8px 0;
            font-weight: bold;
            font-size: 14pt;
        }

        .field-row {
            margin-bottom: 8px;
            line-height: 1.6;
            font-size: 13pt;
        }

        .field-label {
            font-weight: 600;
        }

        .field-value {
            border-bottom: 1px dotted #000;
            display: inline-block;
            min-width: 100px;
            text-align: center;
            padding: 0 6px;
            font-weight: 600;
        }

        .field-value-long {
            min-width: 200px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        td {
            padding: 3px;
            vertical-align: middle;
        }

        .approval-section {
            text-align: center;
            margin: 12px 0;
            padding: 10px;
            background: #fafafa;
            border: 1px solid #ddd;
        }

        .checkbox {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid #000;
            margin-left: 8px;
            vertical-align: middle;
        }

        .notice-box {
            margin: 10px 0;
            padding: 12px;
            background: #fafafa;
            border: 1.2pt solid #ddd;
            line-height: 1.5;
            font-size: 12.5pt;
        }

        .notice-box ul {
            margin: 8px 0 0 20px;
            padding: 0;
        }

        .notice-box li {
            margin-bottom: 5px;
        }

        .signature-area {
            margin-top: 15px;
        }

        .signature-table td {
            text-align: center;
            padding: 5px;
            vertical-align: top;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 150px;
            margin-top: 15px;
        }

        .stamp-container {
            height: 10px;
            text-align: center;
        }

        .stamp-container img {
            width: 3mm;
            height: auto;
        }

        .footer-section {
            width: 100%;
            text-align: center;
            line-height: 0;
            margin: 0;
            padding: 0;
        }

        .footer-section img {
            width: 100%;
            max-width: 100%;
            display: block;
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="page-border">&nbsp;</div>

    <htmlpagefooter name="myFooter">
        <div class="footer-section" style="margin-left: -3mm; margin-right: -3mm;">
            <img src="{{ public_path('images/footer.jpeg') }}" alt="Footer">
        </div>
    </htmlpagefooter>

    <!-- Header -->
    <div class="header-section">
        <img src="{{ public_path('images/file_header.jpeg') }}" alt="Header">
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
            <span class="field-value">{{ $application->section->departments->first()?->name ?? '' }}</span>
        </div>

        <div class="field-row">
            <span class="field-label">أرجو التكرم بالموافقة على طلبي بالتدريب في قسم: </span>
            <span class="field-value field-value-long">{{ $application->section->name ?? '' }}</span>
        </div>

        <table>
            <tr>
                <td width="50%">
                    <span class="field-label">عنوان السكن: </span>
                    <span class="field-value" style="min-width: 150px;">
                        {{ $application->trainee->governorate?->name ?? '' }} -
                        {{ $application->trainee->street ?? '' }}
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
            <span class="field-value">{{ $application->training_hours ?? '' }} ساعة</span>
        </div>

        <div class="approval-section">
            <span class="checkbox"></span>
            <strong>مع الموافقة</strong>
        </div>

        <div class="notice-box">
            نحيطكم علما بانه لا مانع لدينا نحو استيعاب المتدرب المذكور اعلاه ضمن برنامج التدريب في
            {{ $application->section->name ?? '' }}, وذلك ضمن المعايير التالية:
            <ul>
                <li>الإلتزام بتدريب المتدرب تحت إشراف المشرف المكلف من طرفكم.</li>
                <li>الإلتزام بمتابعة دوام المتدرب.</li>
            </ul>
        </div>

        <div class="signature-area">
            <table class="signature-table">
                <tr>
                    <td width="50%">
                        <strong>التوقيع:</strong>
                        <div class="signature-line"></div>
                    </td>
                    <td width="50%">
                        <strong>الختم:</strong>
                        <div class="stamp-container">
                            <img src="{{ public_path('images/stamp.jpeg') }}" alt="Stamp" width="60"
                                style="width: 35mm; height: auto;">
                        </div>
                        <div class="signature-line"></div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>

<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلب التحاق تدريب</title>
    <style>
        @page {
            margin: 20mm 15mm;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 12pt;
            line-height: 1.6;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }

        .header-content {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .header-right,
        .header-left {
            display: table-cell;
            width: 40%;
            vertical-align: middle;
        }

        .header-center {
            display: table-cell;
            width: 20%;
            text-align: center;
            vertical-align: middle;
        }

        .header-right {
            text-align: right;
            font-size: 11pt;
            line-height: 1.8;
        }

        .header-left {
            text-align: left;
            font-size: 10pt;
            line-height: 1.8;
        }

        .logo {
            width: 80px;
            height: 80px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            margin: 20px 0;
            text-decoration: underline;
        }

        .form-section {
            margin: 20px 0;
            line-height: 2.5;
        }

        .form-row {
            margin-bottom: 15px;
            position: relative;
        }

        .underline {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 150px;
            text-align: center;
        }

        .signature-section {
            margin-top: 50px;
            text-align: left;
        }

        .checkbox-group {
            margin: 20px 0;
            text-align: center;
        }

        .checkbox {
            display: inline-block;
            margin: 0 30px;
        }

        .checkbox-circle {
            width: 15px;
            height: 15px;
            border: 1px solid #000;
            border-radius: 50%;
            display: inline-block;
            vertical-align: middle;
            margin-right: 5px;
        }

        .admin-section {
            border: 2px solid #000;
            padding: 15px;
            margin-top: 30px;
        }

        .admin-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9pt;
            border-top: 1px solid #000;
            padding-top: 10px;
        }

        .inline-field {
            display: inline-block;
        }

        .field-label {
            font-weight: normal;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 5px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="header-right">
                كلية تنمية القدرات الجامعية<br>
                جمعية الهلال الأحمر الفلسطيني<br>
                فلسطين – خان يونس
            </div>
            <div class="header-center">
                <img src="{{ public_path('images/logo.png') }}" alt="Logo" class="logo" style="display: none;">
                <div style="width: 80px; height: 80px; border: 2px solid #000; border-radius: 50%; margin: 0 auto;">
                </div>
            </div>
            <div class="header-left">
                University College of Ability Development<br>
                Palestine Red Crescent Society<br>
                Palestine - Khan Younis
            </div>
        </div>
    </div>

    <!-- Title -->
    <div class="title">
        طلب التحاق تدريب امتياز في <span class="underline">مستشفى/مراكز</span> جمعية الهلال الأحمر الفلسطيني
    </div>

    <!-- Form Content -->
    <div class="form-section">
        <div class="form-row" style="margin-top: 20px;">
             أنا الطالب/ة الموقع أدناه <span
                class="underline">{{ $student_name ?? '.....................................................................................................................' }}</span>
        </div>

        <div class="form-row">
            أرجو الكرم بمنافقة الله على طلبي بالتدريب في قسم: <span
                class="underline">{{ $department ?? '..............................................................' }}</span>
        </div>

        <div class="form-row">
            <table>
                <tr>
                    <td style="width: 30%;">الإسم: <span
                            class="underline">{{ $full_name ?? '.......................' }}</span></td>
                    <td style="width: 30%;">الكلية: <span
                            class="underline">{{ $college ?? '.......................' }}</span></td>
                    <td style="width: 40%;"></td>
                </tr>
            </table>
        </div>

        <div class="form-row">
            <table>
                <tr>
                    <td style="width: 50%;">التخصص: <span
                            class="underline">{{ $specialization ?? '..................................................' }}</span>
                    </td>
                    <td style="width: 50%;"></td>
                </tr>
            </table>
        </div>

        <div class="form-row">
            <table>
                <tr>
                    <td style="width: 50%;">عنوان السكن: <span
                            class="underline">{{ $address ?? '..........................................' }}</span></td>
                    <td style="width: 50%;">رقم الجوال: <span
                            class="underline">{{ $mobile ?? '..............................' }}</span></td>
                </tr>
            </table>
        </div>

        <div class="form-row">
            مدة التدريب: <span
                class="underline">{{ $training_duration ?? '.....................................' }}</span>
        </div>

        <div class="form-row" style="margin-top: 30px;">
            <span style="float: left;">التوقيع: <span class="underline"
                    style="min-width: 100px;">{{ $signature ?? '..................' }}</span></span>
        </div>
    </div>

    <!-- Admin Section -->
    <div class="admin-section">
        <div class="admin-title">لإستخدام الإدارة</div>

        <div style="text-align: right; margin-bottom: 15px;">
            السيد/ة عميد كلية تنمية القدرات الجامعية<br>
            مسؤول ملف التدريب والتعليم الطواريء المحترم
        </div>

        <div style="text-align: right; margin: 20px 0;">
            تحية طيبة وبعد،،،
        </div>

        <div class="checkbox-group">
            <div class="checkbox">
                <span class="checkbox-circle"></span>
                <span>نرجو الموافقة</span>
            </div>
            <div class="checkbox">
                <span class="checkbox-circle"></span>
                <span>عدم الموافقة</span>
            </div>
        </div>

        <div style="text-align: right; margin-top: 20px; line-height: 2;">
            بخصوص طلب/ة ......... بحيث استيفت الطالب/ة جميع الشروط المطلوبة<br>
            في ............................................. وشكراً حسب المعايير السائدة.
        </div>

        <div style="margin-top: 30px;">
            <div style="float: right;">
                - الإلتزام بتدريب المتدرب تحت إشراف المشرف الكلينكي من طرفكم
            </div>
        </div>

        <div style="clear: both; margin-top: 15px;">
            <div style="float: right;">
                - الإلتزام بمتابعة دوام المتدرب ومتدها 6 ساعات يوميا
            </div>
        </div>

        <div style="clear: both; margin-top: 50px;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; text-align: right;">
                        الموافق/ة: <span class="underline" style="min-width: 100px;"></span>
                    </td>
                    <td style="width: 50%; text-align: left;">
                        الختم: <span class="underline" style="min-width: 100px;"></span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        جمعية الهلال الأحمر الفلسطيني – خان يونس<br>
        مقابل الأمن الوقائي – بجوار مصنع الخرساني<br>
        Palestine Red Crescent Society – Khan Younis
    </div>
</body>

</html>
<?php

namespace App\Http\Controllers;

use App\Models\Administrative;
use App\Models\Governorate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelTemplateController extends Controller
{
    public function download()
    {
        $spreadsheet = new Spreadsheet();

        // ==========================================
        // SHEET 1: DATA (Hidden)
        // ==========================================
        $dataSheet = $spreadsheet->createSheet();
        $dataSheet->setTitle('Data');

        // Fetch Data
        // Custom Sort Order for Governorates
        $desiredOrder = ['شمال غزة', 'غزة', 'محافظات الوسطى', 'خانيونس', 'رفح'];

        $governorates = Governorate::all()->sortBy(function($gov) use ($desiredOrder) {
            $index = array_search($gov->name, $desiredOrder);
            return $index === false ? 999 : $index; // Put unknown ones at the end
        })->pluck('name')->toArray();

        // Fetch only Active Administratives (SoftDeletes filtered by default) that belong to a valid Governorate
        // Sort them alphabetically to make the dropdown scrolling easier
        $administratives = Administrative::with('governorate')
            ->active()
            ->whereHas('governorate')
            ->get()
            ->sortBy('name') // Sort by name
            ->map(fn($admin) => $admin->name)
            ->toArray();

        // Populate Governorates (Column A)
        $dataSheet->setCellValue('A1', 'Governorates');
        $row = 2;
        foreach ($governorates as $gov) {
            $dataSheet->setCellValue('A' . $row, $gov);
            $row++;
        }
        $govCount = count($governorates) + 1;
        $govRange = "Data!\$A\$2:\$A\${$govCount}";

        // Populate Administratives (Column B)
        $dataSheet->setCellValue('B1', 'Administratives');
        $row = 2;
        foreach ($administratives as $adm) {
            $dataSheet->setCellValue('B' . $row, $adm);
            $row++;
        }
        $admCount = count($administratives) + 1;
        $admRange = "Data!\$B\$2:\$B\${$admCount}";

        // Populate Gender options (Column C)
        $dataSheet->setCellValue('C1', 'Gender');
        $dataSheet->setCellValue('C2', 'ذكر');
        $dataSheet->setCellValue('C3', 'أنثى');
        $genderRange = "Data!\$C\$2:\$C\$3";

        // Hide the data sheet
        // $dataSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

        // ==========================================
        // SHEET 0: TEMPLATE (Visible)
        // ==========================================
        $sheet = $spreadsheet->getSheet(0);
        $sheet->setTitle('Trainee Import Template');

        $sheet->setRightToLeft(true);

        $headers = [
            'A' => ['label' => 'اسم الطالب'], // Student Name
            'B' => ['label' => 'رقم الهوية'], // National ID
            'C' => ['label' => 'الجنس'], // Gender
            'D' => ['label' => 'الرقم الجامعي'], // Student Number
            'E' => ['label' => 'المحافظة'], // Governorate
            'F' => ['label' => 'رقم الجوال (970/2)(59xxxxxxx)'], // Phone Number
            'G' => ['label' => 'تاريخ الميلاد'], // Date of Birth
            'H' => ['label' => 'مكان التدريب'], // Administrative
        ];

        // 1. Set Headers and Styling
        foreach ($headers as $col => $config) {
            $cell = $col . '1';
            $sheet->setCellValue($cell, $config['label']);
            $sheet->getColumnDimension($col)->setAutoSize(true);

            // Header Style
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Arial'], // Use a font that supports Arabic well
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4B5563']], // Gray
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        // 2. Data Validation (Dropdowns)
        // Apply to rows 2-1000
        $rowCount = 1000;

        // Gender Dropdown (Column C)
        $validation = $sheet->getCell('C2')->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1($genderRange);

        // Clone validation to all rows in column C
        for ($i = 2; $i <= $rowCount; $i++) {
            $sheet->getCell("C$i")->setDataValidation(clone $validation);
        }

        // Governorate Dropdown (Column E)
        $validation = $sheet->getCell('E2')->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1($govRange);

        // Clone validation to all rows in column E
        for ($i = 2; $i <= $rowCount; $i++) {
            $sheet->getCell("E$i")->setDataValidation(clone $validation);
        }

        // Administrative Dropdown (Column H)
        $validation = $sheet->getCell('H2')->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1($admRange);

        // Clone validation to all rows in column H
        for ($i = 2; $i <= $rowCount; $i++) {
            $sheet->getCell("H$i")->setDataValidation(clone $validation);
        }

        // 3. Date Validation (Column G)
        // Note: Excel DataValidation for dates is essentially checking if the value is a number/date format
        // We can just format the cells as Text first to catch exact input, or Date format
        $sheet->getStyle('G2:G'.$rowCount)->getNumberFormat()->setFormatCode('yyyy-mm-dd');

        // Add Data Validation for Date to show a 'hint' or force date logic (Note: Excel doesn't have a native 'DatePicker' popup without VBA, but we can restrict input)
        $dateValidation = $sheet->getCell('G2')->getDataValidation();
        $dateValidation->setType(DataValidation::TYPE_DATE);
        $dateValidation->setErrorStyle(DataValidation::STYLE_STOP);
        $dateValidation->setAllowBlank(true);
        $dateValidation->setShowInputMessage(true);
        $dateValidation->setPromptTitle('تاريخ الميلاد'); // Date of Birth
        $dateValidation->setPrompt('الرجاء إدخال التاريخ بصيغة: YYYY-MM-DD'); // Please enter date as YYYY-MM-DD
        $dateValidation->setOperator(DataValidation::OPERATOR_BETWEEN);
        $dateValidation->setFormula1('1900-01-01');
        $dateValidation->setFormula2('2030-12-31');

        for ($i = 2; $i <= $rowCount; $i++) {
            $sheet->getCell("G$i")->setDataValidation(clone $dateValidation);
        }

        // 4. Force Number Format (No Decimals) for IDs and Phones (Columns B, D, F)
        // Using '0' forces Excel to display the full number without scientific notation (E+11)
        $sheet->getStyle('B2:B'.$rowCount)->getNumberFormat()->setFormatCode('0');
        $sheet->getStyle('D2:D'.$rowCount)->getNumberFormat()->setFormatCode('0');
        $sheet->getStyle('F2:F'.$rowCount)->getNumberFormat()->setFormatCode('0');

        // Add Data Validation Hint for Phone Number (Column F)
        $phoneValidation = $sheet->getCell('F2')->getDataValidation();
        $phoneValidation->setType(DataValidation::TYPE_NONE); // Just for the prompt message
        $phoneValidation->setShowInputMessage(true);
        $phoneValidation->setPromptTitle('صيغة رقم الجوال'); // Phone Format
        $phoneValidation->setPrompt('الرجاء اتباع الصيغة: 97(0/2)5(9/6)XXXXXXX' . "\n" . 'مثال: 970591231231'); // Please follow format... Example...

        for ($i = 2; $i <= $rowCount; $i++) {
            $sheet->getCell("F$i")->setDataValidation(clone $phoneValidation);
        }

        // 5. Example Data (Row 2) - Optional, slightly grayed out example
        $sheet->setCellValue('A2', 'أحمد محمد'); // Arabic Name
        $sheet->setCellValue('B2', '123456789');
        // $sheet->setCellValue('C2', 'ذكر'); // Don't prefill gender select
        $sheet->setCellValue('D2', '20230001');
        // $sheet->setCellValue('E2', 'Gaza'); // Don't prefill governorate select
        $sheet->setCellValue('F2', '970591231231'); // Local format example
        $sheet->setCellValue('G2', '2000-01-01');
        // $sheet->setCellValue('H2', 'IT Dept'); // Don't prefill administrative select
        // ==========================================
        // OUTPUT
        // ==========================================
        $fileName = 'قالب_رفع_الطلبات.xlsx';

        $response = new StreamedResponse(function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Proper UTF-8 filename handling for modern browsers
        $disposition = $response->headers->makeDisposition(
            \Symfony\Component\HttpFoundation\HeaderUtils::DISPOSITION_ATTACHMENT,
            $fileName,       // UTF-8 filename (Arabic)
            'template.xlsx'  // Fallback ASCII filename
        );

        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}

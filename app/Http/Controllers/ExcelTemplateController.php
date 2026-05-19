<?php

namespace App\Http\Controllers;

use App\Models\Administrative;
use App\Models\Governorate;
use App\Models\Major;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelTemplateController extends Controller
{
    public function download(): StreamedResponse
    {
        return Auth::user()?->isMinistry()
            ? $this->buildMohTemplate()
            : $this->buildCollegeTemplate();
    }

    private function buildMohTemplate(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();

        $dataSheet = $spreadsheet->createSheet();
        $dataSheet->setTitle('Data');

        $governorates = $this->getGovernorateNames();
        $administratives = $this->getMohAdministrativeNames();

        $this->populateColumn($dataSheet, 'A', 'Governorates', $governorates);
        $this->populateColumn($dataSheet, 'B', 'Administratives', $administratives);
        $this->populateGenderColumn($dataSheet, 'C');

        $govRange = $this->columnRange('Data', 'A', count($governorates));
        $admRange = $this->columnRange('Data', 'B', count($administratives));
        $genderRange = 'Data!$C$2:$C$3';

        $sheet = $spreadsheet->getSheet(0);
        $sheet->setTitle('Trainee Import Template');
        $sheet->setRightToLeft(true);

        $headers = [
            'A' => 'اسم الطالب',
            'B' => 'رقم الهوية',
            'C' => 'الجنس',
            'D' => 'ساعات التدريب',
            'E' => 'المحافظة',
            'F' => 'الشارع',
            'G' => 'رقم الجوال (970/2)(59xxxxxxx)',
            'H' => 'تاريخ الميلاد',
            'I' => 'مكان التدريب',
        ];

        $this->applyHeaders($sheet, $headers);

        $rowCount = 1000;
        $this->applyGenderDropdown($sheet, 'C', $rowCount, $genderRange);
        $this->applyIntegerValidation($sheet, 'D', $rowCount);
        $this->applyGovernorateDropdown($sheet, 'E', $rowCount, $govRange);
        $this->applyAdministrativeDropdown($sheet, 'I', $rowCount, $admRange);
        $this->applyDateFormat($sheet, 'H', $rowCount);
        $this->applyPhoneHint($sheet, 'G', $rowCount);

        $sheet->getStyle('B2:B' . $rowCount)->getNumberFormat()->setFormatCode('0');
        $sheet->getStyle('D2:D' . $rowCount)->getNumberFormat()->setFormatCode('0');
        $sheet->getStyle('G2:G' . $rowCount)->getNumberFormat()->setFormatCode('0');

        $sheet->setCellValue('A2', 'أحمد محمد');
        $sheet->setCellValue('B2', '123456789');
        $sheet->setCellValue('E2', 'غزة');
        $sheet->setCellValue('F2', 'شارع الجلاء');
        $sheet->setCellValue('G2', '970591231231');
        $sheet->setCellValue('H2', '2000-01-01');

        return $this->streamResponse($spreadsheet);
    }

    private function buildCollegeTemplate(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();

        $dataSheet = $spreadsheet->createSheet();
        $dataSheet->setTitle('Data');

        $governorates = $this->getGovernorateNames();
        $administratives = $this->getAllAdministrativeNames();
        $majors = $this->getCollegeMajorNames();

        $this->populateColumn($dataSheet, 'A', 'Governorates', $governorates);
        $this->populateColumn($dataSheet, 'B', 'Administratives', $administratives);
        $this->populateGenderColumn($dataSheet, 'C');
        $this->populateColumn($dataSheet, 'D', 'Majors', $majors);

        $govRange = $this->columnRange('Data', 'A', count($governorates));
        $admRange = $this->columnRange('Data', 'B', count($administratives));
        $genderRange = 'Data!$C$2:$C$3';
        $majorRange = $this->columnRange('Data', 'D', count($majors));

        $sheet = $spreadsheet->getSheet(0);
        $sheet->setTitle('Trainee Import Template');
        $sheet->setRightToLeft(true);

        $headers = [
            'A' => 'اسم الطالب',
            'B' => 'رقم الهوية',
            'C' => 'الجنس',
            'D' => 'الرقم الجامعي',
            'E' => 'التخصص',
            'F' => 'ساعات التدريب',
            'G' => 'المحافظة',
            'H' => 'الشارع',
            'I' => 'رقم الجوال (970/2)(59xxxxxxx)',
            'J' => 'تاريخ الميلاد',
            'K' => 'مكان التدريب',
        ];

        $this->applyHeaders($sheet, $headers);

        $rowCount = 1000;
        $this->applyGenderDropdown($sheet, 'C', $rowCount, $genderRange);
        $this->applyMajorDropdown($sheet, 'E', $rowCount, $majorRange);
        $this->applyIntegerValidation($sheet, 'F', $rowCount);
        $this->applyGovernorateDropdown($sheet, 'G', $rowCount, $govRange);
        $this->applyAdministrativeDropdown($sheet, 'K', $rowCount, $admRange);
        $this->applyDateFormat($sheet, 'J', $rowCount);
        $this->applyPhoneHint($sheet, 'I', $rowCount);

        $sheet->getStyle('B2:B' . $rowCount)->getNumberFormat()->setFormatCode('0');
        $sheet->getStyle('D2:D' . $rowCount)->getNumberFormat()->setFormatCode('0');
        $sheet->getStyle('F2:F' . $rowCount)->getNumberFormat()->setFormatCode('0');
        $sheet->getStyle('I2:I' . $rowCount)->getNumberFormat()->setFormatCode('0');

        $sheet->setCellValue('A2', 'أحمد محمد');
        $sheet->setCellValue('B2', '123456789');
        $sheet->setCellValue('D2', '20230001');
        $sheet->setCellValue('E2', $majors[0] ?? 'التمريض');
        $sheet->setCellValue('F2', '120');
        $sheet->setCellValue('G2', 'غزة');
        $sheet->setCellValue('H2', 'شارع الوحدة');
        $sheet->setCellValue('I2', '970591231231');
        $sheet->setCellValue('J2', '2000-01-01');

        return $this->streamResponse($spreadsheet);
    }

    private function getCollegeMajorNames(): array
    {
        $user = Auth::user();

        if ($user && $user->isCollegeSupervisor() && $user->college) {
            return $user->college->majors()
                ->wherePivot('active', true)
                ->pluck('name')
                ->toArray();
        }

        return Major::pluck('name')->toArray();
    }

    private function getGovernorateNames(): array
    {
        $desiredOrder = ['شمال غزة', 'غزة', 'محافظات الوسطى', 'خانيونس', 'رفح'];

        return Governorate::all()
            ->sortBy(fn($gov) => ($i = array_search($gov->name, $desiredOrder)) === false ? 999 : $i)
            ->pluck('name')
            ->toArray();
    }

    private function getAllAdministrativeNames(): array
    {
        return Administrative::with('governorate')
            ->active()
            ->whereHas('governorate')
            ->get()
            ->sortBy('name')
            ->map(fn($a) => $a->name)
            ->toArray();
    }

    private function getMohAdministrativeNames(): array
    {
        return Administrative::with('governorate')
            ->active()
            ->whereHas('governorate')
            ->whereHas('sections', fn($q) => $q->whereHas(
                'departments',
                fn($d) => $d->where('is_medical', true)
            ))
            ->get()
            ->sortBy('name')
            ->map(fn($a) => $a->name)
            ->toArray();
    }

    private function populateColumn(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $col, string $header, array $values): void
    {
        $sheet->setCellValue($col . '1', $header);
        $row = 2;
        foreach ($values as $value) {
            $sheet->setCellValue($col . $row, $value);
            $row++;
        }
    }

    private function populateGenderColumn(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $col): void
    {
        $sheet->setCellValue($col . '1', 'Gender');
        $sheet->setCellValue($col . '2', 'ذكر');
        $sheet->setCellValue($col . '3', 'أنثى');
    }

    private function columnRange(string $sheetName, string $col, int $count): string
    {
        return "{$sheetName}!\${$col}\$2:\${$col}\$" . ($count + 1);
    }

    private function applyHeaders(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $headers): void
    {
        foreach ($headers as $col => $label) {
            $cell = $col . '1';
            $sheet->setCellValue($cell, $label);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Arial'],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4B5563']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }
    }

    private function applyGenderDropdown(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $col, int $rows, string $range): void
    {
        $v = $this->makeListValidation($range);
        for ($i = 2; $i <= $rows; $i++) {
            $sheet->getCell("{$col}{$i}")->setDataValidation(clone $v);
        }
    }

    private function applyMajorDropdown(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $col, int $rows, string $range): void
    {
        $v = $this->makeListValidation($range);
        for ($i = 2; $i <= $rows; $i++) {
            $sheet->getCell("{$col}{$i}")->setDataValidation(clone $v);
        }
    }

    private function applyGovernorateDropdown(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $col, int $rows, string $range): void
    {
        $v = $this->makeListValidation($range);
        for ($i = 2; $i <= $rows; $i++) {
            $sheet->getCell("{$col}{$i}")->setDataValidation(clone $v);
        }
    }

    private function applyAdministrativeDropdown(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $col, int $rows, string $range): void
    {
        $v = $this->makeListValidation($range);
        for ($i = 2; $i <= $rows; $i++) {
            $sheet->getCell("{$col}{$i}")->setDataValidation(clone $v);
        }
    }

    private function makeListValidation(string $formula): DataValidation
    {
        $v = new DataValidation();
        $v->setType(DataValidation::TYPE_LIST);
        $v->setErrorStyle(DataValidation::STYLE_STOP);
        $v->setAllowBlank(true);
        $v->setShowInputMessage(true);
        $v->setShowErrorMessage(true);
        $v->setShowDropDown(true);
        $v->setFormula1($formula);
        return $v;
    }

    private function applyDateFormat(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $col, int $rows): void
    {
        $sheet->getStyle("{$col}2:{$col}{$rows}")->getNumberFormat()->setFormatCode('yyyy-mm-dd');

        $v = new DataValidation();
        $v->setType(DataValidation::TYPE_DATE);
        $v->setErrorStyle(DataValidation::STYLE_STOP);
        $v->setAllowBlank(true);
        $v->setShowInputMessage(true);
        $v->setPromptTitle('تاريخ الميلاد');
        $v->setPrompt('الرجاء إدخال التاريخ بصيغة: YYYY-MM-DD');
        $v->setOperator(DataValidation::OPERATOR_BETWEEN);
        $v->setFormula1('1900-01-01');
        $v->setFormula2('2030-12-31');

        for ($i = 2; $i <= $rows; $i++) {
            $sheet->getCell("{$col}{$i}")->setDataValidation(clone $v);
        }
    }

    private function applyIntegerValidation(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $col, int $rows): void
    {
        $v = new DataValidation();
        $v->setType(DataValidation::TYPE_WHOLE);
        $v->setErrorStyle(DataValidation::STYLE_STOP);
        $v->setAllowBlank(true);
        $v->setShowInputMessage(true);
        $v->setShowErrorMessage(true);

        $v->setErrorTitle(__('validation.attributes.training_hours'));
        $v->setError(__('validation.custom.training_hours.max', ['attribute' => __('validation.attributes.training_hours'), 'max' => 1000]));

        $v->setPromptTitle(__('validation.attributes.training_hours'));
        $v->setPrompt('الرجاء إدخال عدد ساعات صحيح بين 1 و 1000');

        $v->setOperator(DataValidation::OPERATOR_BETWEEN);
        $v->setFormula1('1');
        $v->setFormula2('1000');

        for ($i = 2; $i <= $rows; $i++) {
            $sheet->getCell("{$col}{$i}")->setDataValidation(clone $v);
        }
    }

    private function applyPhoneHint(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $col, int $rows): void
    {
        $v = new DataValidation();
        $v->setType(DataValidation::TYPE_NONE);
        $v->setShowInputMessage(true);
        $v->setPromptTitle('صيغة رقم الجوال');
        $v->setPrompt('الرجاء اتباع الصيغة: 97(0/2)5(9/6)XXXXXXX' . "\n" . 'مثال: 970591231231');

        for ($i = 2; $i <= $rows; $i++) {
            $sheet->getCell("{$col}{$i}")->setDataValidation(clone $v);
        }
    }

    private function streamResponse(Spreadsheet $spreadsheet): StreamedResponse
    {
        $fileName = 'قالب_رفع_الطلبات.xlsx';

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $disposition = $response->headers->makeDisposition(
            \Symfony\Component\HttpFoundation\HeaderUtils::DISPOSITION_ATTACHMENT,
            $fileName,
            'template.xlsx'
        );

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}

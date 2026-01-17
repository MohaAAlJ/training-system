<?php

namespace App\Filament\Exporters;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class StyleExportFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $disk;

    public function __construct($filePath, $disk = 'local')
    {
        $this->filePath = $filePath;
        $this->disk = $disk;
        $this->delay(now()->addSeconds(2)); // Delay to ensure Filament has finished writing the file
    }

    public function handle(): void
    {
        try {
            $storage = Storage::disk($this->disk);

            if (!$storage->exists($this->filePath)) {
                Log::warning('Export file not found for styling: ' . $this->filePath);
                return;
            }

            $fullPath = $storage->path($this->filePath);
            Log::info('Applying RTL and alignment styling to: ' . $fullPath);

            $spreadsheet = IOFactory::load($fullPath);
            $worksheet = $spreadsheet->getActiveSheet();

            // 1. Set RTL for the entire sheet (Columns A, B, C start from the right)
            $worksheet->setRightToLeft(true);

            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
            $lastColumnLetter = Coordinate::stringFromColumnIndex($highestColumnIndex);

            // 2. Insert header image and rows at top
            $worksheet->insertNewRowBefore(1, 5);
            $highestRow += 5;

            // Add header image to row 1 - merge cells across all columns
            $headerImagePath = public_path('images/file_header.jpeg');
            if (file_exists($headerImagePath)) {
                // Merge the first 3 rows across all columns for the image
                $worksheet->mergeCells('A1:' . $lastColumnLetter . '3');

                $drawing = new Drawing();
                $drawing->setPath($headerImagePath);
                $drawing->setHeight(180); // Height in pixels - increased
                $drawing->setWidth(1200); // Width to span all columns - increased
                $drawing->setCoordinates('A1');
                $worksheet->getDrawingCollection()->append($drawing);
                $worksheet->getRowDimension(1)->setRowHeight(90);
                $worksheet->getRowDimension(2)->setRowHeight(90);
                $worksheet->getRowDimension(3)->setRowHeight(90);
            }

            // Merge cells for title (row 4)
            $worksheet->mergeCells('A4:' . $lastColumnLetter . '4');
            $worksheet->setCellValue('A4', 'بيانات الطلبات');

            // Style title row - dark red background, white bold text
            $worksheet->getStyle('A4:' . $lastColumnLetter . '4')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'readOrder' => Alignment::READORDER_RTL,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'C00000']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THICK,
                        'color' => ['rgb' => '000000']
                    ]
                ],
            ]);
            $worksheet->getRowDimension(4)->setRowHeight(40);

            // 3. Style header row (now row 5) - light red, bold white text
            $worksheet->getStyle('A5:' . $lastColumnLetter . '5')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'readOrder' => Alignment::READORDER_RTL,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FF6B6B']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THICK,
                        'color' => ['rgb' => '000000']
                    ]
                ],
            ]);
            $worksheet->getRowDimension(5)->setRowHeight(30);

            // 4. Style data rows - thick borders and right alignment
            if ($highestRow >= 6) {
                $dataRange = 'A6:' . $lastColumnLetter . $highestRow;
                $worksheet->getStyle($dataRange)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'readOrder' => Alignment::READORDER_RTL,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['rgb' => '000000']
                        ]
                    ],
                ]);

                // Set row heights for data
                for ($i = 6; $i <= $highestRow; $i++) {
                    $worksheet->getRowDimension($i)->setRowHeight(25);
                }
            }

            // 5. Set column widths
            for ($i = 1; $i <= $highestColumnIndex; $i++) {
                $column = Coordinate::stringFromColumnIndex($i);
                $worksheet->getColumnDimension($column)->setWidth(22);
            }

            // Save the styled file
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($fullPath);

            Log::info('✅ Excel styling (RTL & Right Alignment) applied successfully to: ' . $fullPath);
        } catch (\Exception $e) {
            Log::error('❌ Excel styling failed: ' . $e->getMessage());
        }
    }
}

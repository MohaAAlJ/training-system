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

            // 2. Insert title row at top
            $worksheet->insertNewRowBefore(1);
            $highestRow++;

            // Merge cells for title
            $worksheet->mergeCells('A1:' . $lastColumnLetter . '1');
            $worksheet->setCellValue('A1', 'بيانات الطلبات');

            // Style title row - dark blue background, white bold text
            $worksheet->getStyle('A1:' . $lastColumnLetter . '1')->applyFromArray([
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
                    'startColor' => ['rgb' => '2F5496']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THICK, 
                        'color' => ['rgb' => '000000']
                    ]
                ],
            ]);
            $worksheet->getRowDimension(1)->setRowHeight(40);

            // 3. Style header row (now row 2) - light blue, bold white text
            $worksheet->getStyle('A2:' . $lastColumnLetter . '2')->applyFromArray([
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
                    'startColor' => ['rgb' => '4472C4']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THICK, 
                        'color' => ['rgb' => '000000']
                    ]
                ],
            ]);
            $worksheet->getRowDimension(2)->setRowHeight(30);

            // 4. Style data rows - thick borders and right alignment
            if ($highestRow >= 3) {
                $dataRange = 'A3:' . $lastColumnLetter . $highestRow;
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
                for ($i = 3; $i <= $highestRow; $i++) {
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

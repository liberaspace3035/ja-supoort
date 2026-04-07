<?php

namespace App\Services;

use App\Models\Survey;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExcelExportService
{
    private const START_ROW = 2;
    private const MAP_COLUMN = 'E';
    private const FIRST_IMAGE_COLUMN_INDEX = 6;
    private const MAX_IMAGES = 10;
    private const IMAGE_HEIGHT = 150;

    public function download(Collection $surveys): BinaryFileResponse
    {
        $spreadsheet = $this->loadTemplate();
        $templateSheet = $spreadsheet->getSheet(0);
        $grouped = $surveys->groupBy(fn (Survey $survey): string => $survey->survey_date->format('Y-m-d'));
        $isFirstSheetUsed = false;

        foreach ($grouped as $date => $items) {
            $sheet = $isFirstSheetUsed ? clone $templateSheet : $templateSheet;
            $sheet->setTitle(Carbon::parse($date)->format('m-d'));
            $this->fillSheet($sheet, $items);

            if ($isFirstSheetUsed) {
                $spreadsheet->addSheet($sheet);
            }
            $isFirstSheetUsed = true;
        }

        if (!$isFirstSheetUsed) {
            $templateSheet->setTitle('No Data');
        }

        $tempPath = storage_path('app/private/exports/surveys_' . now()->format('Ymd_His') . '.xlsx');
        $directory = dirname($tempPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($tempPath);
        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    private function loadTemplate(): Spreadsheet
    {
        $templatePath = storage_path('app/templates/survey_template.xlsx');
        if (is_file($templatePath)) {
            return IOFactory::load($templatePath);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template');
        $sheet->fromArray(['生産者名', '品種名', '調査日', '生育状況', '地図', '写真1'], null, 'A1');

        return $spreadsheet;
    }

    private function fillSheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, Collection $items): void
    {
        $row = self::START_ROW;
        foreach ($items as $survey) {
            $sheet->setCellValue("A{$row}", $survey->farmer_name);
            $sheet->setCellValue("B{$row}", $survey->variety_name);
            $sheet->setCellValue("C{$row}", $survey->survey_date->format('Y-m-d'));
            $sheet->setCellValue("D{$row}", $survey->growth_status);
            $sheet->setCellValue(
                self::MAP_COLUMN . $row,
                sprintf('=HYPERLINK("https://www.google.com/maps?q=%s,%s","地図")', $survey->latitude, $survey->longitude)
            );

            $sheet->getRowDimension($row)->setRowHeight(self::IMAGE_HEIGHT + 10);
            $this->embedImages($sheet, $survey, $row);
            $row++;
        }
    }

    private function embedImages(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, Survey $survey, int $row): void
    {
        $photos = array_slice($survey->photos ?? [], 0, self::MAX_IMAGES);

        foreach ($photos as $index => $relativePath) {
            if (!Storage::disk('local')->exists($relativePath)) {
                continue;
            }

            $column = Coordinate::stringFromColumnIndex(self::FIRST_IMAGE_COLUMN_INDEX + $index);
            $drawing = new Drawing();
            $drawing->setName('SurveyPhoto_' . $survey->id . '_' . $index);
            $drawing->setPath(Storage::disk('local')->path($relativePath));
            $drawing->setCoordinates($column . $row);
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);
            $drawing->setResizeProportional(true);
            $drawing->setHeight(self::IMAGE_HEIGHT);
            $drawing->setWorksheet($sheet);

            $sheet->getColumnDimension($column)->setWidth(24);
        }
    }
}

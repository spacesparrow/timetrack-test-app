<?php

declare(strict_types=1);

namespace App\Service\Export;

use App\DTO\TasksExportDTO;
use App\DTO\TasksExportResponseDTO;
use App\Exception\UnsupportedExportFormatException;
use App\Traits\TasksExportTrait;
use App\Traits\TranslatorInterfaceAwareTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class ExcelTasksExportService implements TasksExportServiceInterface
{
    use TasksExportTrait;
    use TranslatorInterfaceAwareTrait;

    /** @var string[] */
    private const HEADERS = [
        'ID',
        'Date',
        'Title',
        'Comment',
        'Time spent',
    ];

    /*
     * Row index where content will start
     */
    private const CONTENT_START_ROW = 2;

    /*
     * Supports export if XLSX type requested
     */
    public function supports(TasksExportDTO $tasksExportDTO): bool
    {
        return $tasksExportDTO->isExcelExport();
    }

    /**
     * @throws UnsupportedExportFormatException
     * @throws Exception
     */
    public function export(TasksExportDTO $tasksExportDTO): TasksExportResponseDTO
    {
        /* prepare export file content */
        $spreadsheet = $this->createFilledTemplate($tasksExportDTO);

        /* create writer object to store data in XLSX file */
        $writer = new XlsxWriter($spreadsheet);
        /* create temporary file with .xlsx extension */
        $filename = $this->createFilename(self::TYPE_XLSX);
        $tempFile = $this->createTempFile($filename);
        /* write content to file */
        $writer->save($tempFile);

        return $this->generateResponseDTO($filename, $tempFile);
    }

    public function createFilledTemplate(TasksExportDTO $tasksExportDTO): Spreadsheet
    {
        /* create empty Spreadsheet object */
        $spreadsheet = new Spreadsheet();
        /* get active sheet and write column headers */
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(self::HEADERS);
        /* here will be stored index of row for filling */
        $cellIndex = self::CONTENT_START_ROW;

        /* write tasks data to sheet, each task as row */
        foreach ($tasksExportDTO->getTasks() as $task) {
            $sheet->fromArray(
                $task->toExportArray(),
                null,
                "A$cellIndex"
            );
            /* increase index of row for filling */
            ++$cellIndex;
        }

        /* add row with total time spent at the end of file */
        $sheet->fromArray(
            [
                $this->getTranslator()->trans('Total spent'),
                null,
                null,
                null,
                $tasksExportDTO->getTotalTimeSpent(),
            ],
            null,
            "A$cellIndex"
        );

        return $spreadsheet;
    }
}

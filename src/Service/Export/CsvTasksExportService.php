<?php

declare(strict_types=1);

namespace App\Service\Export;

use App\DTO\TasksExportDTO;
use App\DTO\TasksExportResponseDTO;
use App\Exception\UnsupportedExportFormatException;
use App\Traits\TasksExportTrait;
use App\Traits\TranslatorInterfaceAwareTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;

class CsvTasksExportService implements TasksExportServiceInterface
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

    public function supports(TasksExportDTO $tasksExportDTO): bool
    {
        return $tasksExportDTO->isCsvExport();
    }

    /**
     * @throws UnsupportedExportFormatException
     */
    public function export(TasksExportDTO $tasksExportDTO): TasksExportResponseDTO
    {
        $spreadsheet = $this->createFilledTemplate($tasksExportDTO);

        $writer = new CsvWriter($spreadsheet);
        $filename = $this->createFilename(self::TYPE_CSV);
        $tempFile = $this->createTempFile($filename);
        $writer->save($tempFile);

        return $this->generateResponseDTO($filename, $tempFile);
    }

    public function createFilledTemplate(TasksExportDTO $tasksExportDTO): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(self::HEADERS);
        $cellIndex = 2;

        foreach ($tasksExportDTO->getTasks() as $task) {
            $sheet->fromArray(
                $task->toExportArray(),
                null,
                "A$cellIndex"
            );
            ++$cellIndex;
        }

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

<?php

declare(strict_types=1);

namespace App\Service\Export;

use App\DTO\TasksExportDTO;
use App\DTO\TasksExportResponseDTO;
use App\Exception\UnsupportedExportFormatException;
use App\Traits\TranslatorInterfaceAwareTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class BaseTasksExportService
{
    use TranslatorInterfaceAwareTrait;

    protected function createFilledSpreadsheet(TasksExportDTO $tasksExportDTO): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(TasksExportServiceInterface::HEADERS);
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

    protected function createTempFile(string $filename): string
    {
        return tempnam(sys_get_temp_dir(), $filename);
    }

    /**
     * @throws UnsupportedExportFormatException
     */
    protected function createFilename(string $extension): string
    {
        if (!in_array($extension, TasksExportServiceInterface::ALLOWED_TYPES)) {
            throw new UnsupportedExportFormatException($extension);
        }

        return "tasks_export.$extension";
    }

    protected function generateResponseDTO(string $filename, string $tempFile): TasksExportResponseDTO
    {
        return new TasksExportResponseDTO($filename, $tempFile);
    }
}

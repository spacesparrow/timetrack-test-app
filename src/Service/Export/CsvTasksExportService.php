<?php

declare(strict_types=1);

namespace App\Service\Export;

use App\DTO\TasksExportDTO;
use App\DTO\TasksExportResponseDTO;
use App\Exception\UnsupportedExportFormatException;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;

class CsvTasksExportService extends BaseTasksExportService implements TasksExportServiceInterface
{
    public function supports(TasksExportDTO $tasksExportDTO): bool
    {
        return $tasksExportDTO->isCsvExport();
    }

    /**
     * @throws UnsupportedExportFormatException
     */
    public function export(TasksExportDTO $tasksExportDTO): TasksExportResponseDTO
    {
        $spreadsheet = $this->createFilledSpreadsheet($tasksExportDTO);

        $writer = new CsvWriter($spreadsheet);
        $filename = $this->createFilename(self::TYPE_CSV);
        $tempFile = $this->createTempFile($filename);
        $writer->save($tempFile);

        return $this->generateResponseDTO($filename, $tempFile);
    }
}

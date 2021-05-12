<?php

declare(strict_types=1);

namespace App\Service\Export;

use App\DTO\TasksExportDTO;
use App\DTO\TasksExportResponseDTO;
use App\Exception\UnsupportedExportFormatException;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class ExcelTasksExportService extends BaseTasksExportService implements TasksExportServiceInterface
{
    /**
     * @param TasksExportDTO $tasksExportDTO
     * @return bool
     */
    public function supports(TasksExportDTO $tasksExportDTO): bool
    {
        return $tasksExportDTO->isExcelExport();
    }

    /**
     * @param TasksExportDTO $tasksExportDTO
     * @return TasksExportResponseDTO
     * @throws UnsupportedExportFormatException
     * @throws Exception
     */
    public function export(TasksExportDTO $tasksExportDTO): TasksExportResponseDTO
    {
        $spreadsheet = $this->createFilledSpreadsheet($tasksExportDTO);

        $writer = new XlsxWriter($spreadsheet);
        $filename = $this->createFilename(self::TYPE_XLSX);
        $tempFile = $this->createTempFile($filename);
        $writer->save($tempFile);

        return $this->generateResponseDTO($filename, $tempFile);
    }
}
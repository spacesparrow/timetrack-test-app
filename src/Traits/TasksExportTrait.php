<?php

declare(strict_types=1);

namespace App\Traits;

use App\DTO\TasksExportResponseDTO;
use App\Exception\UnsupportedExportFormatException;
use App\Service\Export\TasksExportServiceInterface;

trait TasksExportTrait
{
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
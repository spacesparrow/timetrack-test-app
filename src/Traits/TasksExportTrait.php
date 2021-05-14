<?php

declare(strict_types=1);

namespace App\Traits;

use App\DTO\TasksExportResponseDTO;
use App\Exception\UnsupportedExportFormatException;
use App\Service\Export\TasksExportServiceInterface;

/*
 * Here stored methods that are widely used in tasks export procedure
 */
trait TasksExportTrait
{
    /*
     * Generate path to temporary file
     */
    protected function createTempFile(string $filename): string
    {
        return tempnam(sys_get_temp_dir(), $filename);
    }

    /**
     * @throws UnsupportedExportFormatException
     */
    protected function createFilename(string $extension): string
    {
        /*
         * Throw exception if requested export type (extension) is not supported
         */
        if (!in_array($extension, TasksExportServiceInterface::ALLOWED_TYPES)) {
            throw new UnsupportedExportFormatException($extension);
        }

        return "tasks_export.$extension";
    }

    /*
     * Created data transfer object with data needed to return file download response
     */
    protected function generateResponseDTO(string $filename, string $tempFile): TasksExportResponseDTO
    {
        return new TasksExportResponseDTO($filename, $tempFile);
    }
}

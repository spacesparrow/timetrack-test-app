<?php

declare(strict_types=1);

namespace App\Service\Export;

use App\DTO\TasksExportDTO;
use App\DTO\TasksExportResponseDTO;
use App\Exception\UnsupportedExportFormatException;

class TasksExportServiceFacade
{
    /** @var iterable|TasksExportServiceInterface[] */
    private iterable $exportServices;

    /**
     * TasksExportServiceFacade constructor.
     */
    public function __construct(iterable $exportServices)
    {
        $this->exportServices = $exportServices;
    }

    /**
     * @throws UnsupportedExportFormatException
     */
    public function export(TasksExportDTO $tasksExportDTO): TasksExportResponseDTO
    {
        /*
         * Loop over tasks export services
         * Check if service supports requested export type (stored in TasksExportDTO)
         * Process export
         */
        foreach ($this->exportServices as $exportService) {
            if ($exportService->supports($tasksExportDTO)) {
                return $exportService->export($tasksExportDTO);
            }
        }

        /*
         * Throw exception if there is no support of requested export type
         */
        throw new UnsupportedExportFormatException($tasksExportDTO->getType());
    }
}

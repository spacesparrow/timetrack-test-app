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
     * @param iterable $exportServices
     */
    public function __construct(iterable $exportServices)
    {
        $this->exportServices = $exportServices;
    }

    /**
     * @param TasksExportDTO $tasksExportDTO
     * @return TasksExportResponseDTO
     * @throws UnsupportedExportFormatException
     */
    public function export(TasksExportDTO $tasksExportDTO): TasksExportResponseDTO
    {
        foreach ($this->exportServices as $exportService) {
            if ($exportService->supports($tasksExportDTO)) {
                return $exportService->export($tasksExportDTO);
            }
        }

        throw new UnsupportedExportFormatException($tasksExportDTO->getType());
    }
}
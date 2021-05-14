<?php

declare(strict_types=1);

namespace App\Service\Export;

use App\DTO\TasksExportDTO;
use App\DTO\TasksExportResponseDTO;

interface TasksExportServiceInterface
{
    /** @var string */
    public const TYPE_PDF = 'pdf';
    /** @var string */
    public const TYPE_CSV = 'csv';
    /** @var string */
    public const TYPE_XLSX = 'xlsx';

    /** @var string[] */
    public const ALLOWED_TYPES = [
        self::TYPE_PDF,
        self::TYPE_CSV,
        self::TYPE_XLSX,
    ];

    public function supports(TasksExportDTO $tasksExportDTO): bool;

    public function export(TasksExportDTO $tasksExportDTO): TasksExportResponseDTO;

    public function createFilledTemplate(TasksExportDTO $tasksExportDTO);
}

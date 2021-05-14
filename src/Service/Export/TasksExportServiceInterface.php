<?php

declare(strict_types=1);

namespace App\Service\Export;

use App\DTO\TasksExportDTO;
use App\DTO\TasksExportResponseDTO;

/*
 * Tasks export interface describes methods to process it
 */
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

    /*
     * Method to check if implementing class supports export of provided type (stored in TasksExportDTO)
     */
    public function supports(TasksExportDTO $tasksExportDTO): bool;

    /*
     * Method to process export procedure of provided tasks (stored in TasksExportDTO)
     */
    public function export(TasksExportDTO $tasksExportDTO): TasksExportResponseDTO;

    /*
     * Method to create and fill with data main part (template) of export file
     */
    public function createFilledTemplate(TasksExportDTO $tasksExportDTO);
}

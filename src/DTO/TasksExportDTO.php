<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Task;
use App\Service\Export\TasksExportServiceInterface;
use DateTime;
use Doctrine\Common\Collections\Collection;

/*
 * Store form data for tasks export action
 */
class TasksExportDTO
{
    /*
     * Export filetype (extension)
     */
    private string $type;

    /*
     * User tasks to export
     */
    /** @var iterable|Task[]|Collection */
    private iterable $tasks;

    /*
     * Start date filter
     */
    private DateTime $startDate;

    /*
     * End date filter
     */
    private DateTime $endDate;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return iterable|Task[]|Collection
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param Task[]|Collection $tasks
     */
    public function setTasks($tasks): void
    {
        $this->tasks = $tasks;
    }

    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    /*
     * Check if .pdf file should be result of export
     */
    public function isPdfExport(): bool
    {
        return TasksExportServiceInterface::TYPE_PDF === $this->type;
    }

    /*
     * Check if .csv file should be result of export
     */
    public function isCsvExport(): bool
    {
        return TasksExportServiceInterface::TYPE_CSV === $this->type;
    }

    /*
     * Check if .xlsx file should be result of export
     */
    public function isExcelExport(): bool
    {
        return TasksExportServiceInterface::TYPE_XLSX === $this->type;
    }

    /*
     * Calculate total time spent
     */
    public function getTotalTimeSpent(): int
    {
        $total = 0;

        foreach ($this->tasks as $task) {
            $total += $task->getTimeSpent();
        }

        return $total;
    }
}

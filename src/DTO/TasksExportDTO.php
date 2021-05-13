<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Task;
use App\Service\Export\TasksExportServiceInterface;
use DateTime;
use Doctrine\Common\Collections\Collection;

class TasksExportDTO
{
    /** @var string */
    private string $type;

    /** @var iterable|Task[]|Collection */
    private iterable $tasks;

    /** @var DateTime */
    private DateTime $startDate;

    /** @var DateTime */
    private DateTime $endDate;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
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

    /**
     * @return DateTime
     */
    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    /**
     * @param DateTime $startDate
     */
    public function setStartDate(DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @return DateTime
     */
    public function getEndDate(): DateTime
    {
        return $this->endDate;
    }

    /**
     * @param DateTime $endDate
     */
    public function setEndDate(DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    /**
     * @return bool
     */
    public function isPdfExport(): bool
    {
        return $this->type === TasksExportServiceInterface::TYPE_PDF;
    }

    /**
     * @return bool
     */
    public function isCsvExport(): bool
    {
        return $this->type === TasksExportServiceInterface::TYPE_CSV;
    }

    /**
     * @return bool
     */
    public function isExcelExport(): bool
    {
        return $this->type === TasksExportServiceInterface::TYPE_XLSX;
    }

    /**
     * @return int
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
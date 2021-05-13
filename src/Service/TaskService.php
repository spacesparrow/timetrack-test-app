<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Task;
use DateTime;
use Doctrine\Common\Collections\Collection;

class TaskService
{
    /**
     * @param Collection $tasks
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return Collection
     */
    public function filterTasksByDateRange(Collection $tasks, DateTime $startDate, DateTime $endDate): Collection
    {
        return $tasks->filter(function (Task $task) use ($startDate, $endDate) {
            return $task->getCreatedDate() >= $startDate && $task->getCreatedDate() <= $endDate;
        });
    }
}
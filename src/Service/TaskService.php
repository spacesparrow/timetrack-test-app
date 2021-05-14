<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\TaskRepository;
use DateTime;
use Doctrine\Common\Collections\Collection;

class TaskService
{
    private TaskRepository $taskRepository;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function getUserTasks(User $user): Collection
    {
        return $this->taskRepository->findByUser($user);
    }

    public function getUserTasksByDateRange(User $user, DateTime $startDate, DateTime $endDate): Collection
    {
        return $this->taskRepository->findUserTasksFilteredByDateRange($user, $startDate, $endDate);
    }
}

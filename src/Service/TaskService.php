<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\TaskRepository;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query;

class TaskService
{
    private TaskRepository $taskRepository;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function getUserTasks(User $user): Collection
    {
        /*
         * Select user tasks from database
         */
        return $this->taskRepository->findByUser($user);
    }

    public function getUserTasksByDateRange(User $user, DateTime $startDate, DateTime $endDate): Collection
    {
        /*
         * Select user tasks, filtered by date range, from database
         */
        return $this->taskRepository->findUserTasksFilteredByDateRange($user, $startDate, $endDate);
    }

    public function getUserTasksQuery(User $user): Query
    {
        /*
         * Return Query object for paginating user tasks
         */
        return $this->taskRepository->findByUserQuery($user);
    }
}

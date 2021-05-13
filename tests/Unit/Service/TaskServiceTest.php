<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Task;
use App\Service\TaskService;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class TaskServiceTest extends TestCase
{
    /**
     * @covers       \App\Service\TaskService::filterTasksByDateRange
     * @dataProvider dataProviderForFilterTasksByDateRangeTesting
     * @param ArrayCollection $tasks
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param int $expectedCount
     */
    public function testFilterTasksByDateRange(
        ArrayCollection $tasks,
        DateTime $startDate,
        DateTime $endDate,
        int $expectedCount
    ): void {
        $filteredTasks = (new TaskService())->filterTasksByDateRange($tasks, $startDate, $endDate);

        static::assertSame(
            $expectedCount,
            $filteredTasks->count()
        );
    }

    /**
     * @return array[]
     */
    public function dataProviderForFilterTasksByDateRangeTesting(): array
    {
        $firstTask = new Task();
        $firstTask->setCreatedDate(new DateTime('2021-05-08'));
        $secondTask = new Task();
        $secondTask->setCreatedDate(new DateTime('2021-05-15'));
        $tasksCollection = new ArrayCollection([$firstTask, $secondTask]);

        return [
            'none filtered out' => [
                $tasksCollection,
                new DateTime('2020-01-01'),
                new DateTime('2021-05-20'),
                2
            ],
            'one filtered out' => [
                $tasksCollection,
                new DateTime('2020-01-01'),
                new DateTime('2021-05-13'),
                1
            ],
            'all filtered out' => [
                $tasksCollection,
                new DateTime('2020-01-01'),
                new DateTime('2020-05-20'),
                0
            ],
        ];
    }
}
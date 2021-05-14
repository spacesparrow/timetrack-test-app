<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Service\TaskService;
use App\Tests\Traits\ReflectionTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class TaskServiceTest extends TestCase
{
    use ReflectionTrait;

    /** @var TaskRepository|MockObject */
    private $taskRepositoryMock;

    /**
     * @covers \App\Service\TaskService::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        $service = $this->getTaskService();

        static::assertInstanceOf(
            TaskRepository::class,
            $this->getNonPublicPropertyValue($service, 'taskRepository')
        );
    }

    /**
     * @covers \App\Service\TaskService::getUserTasks
     * @dataProvider dataProviderForGetUserTasksTesting
     * @param User $user
     * @param Collection $tasks
     */
    public function testGetUserTasks(User $user, Collection $tasks): void
    {
        $this->taskRepositoryMock->expects(static::once())
            ->method('findByUser')
            ->with(...[$user])
            ->willReturn($tasks);

        $this->getTaskService()->getUserTasks($user);
    }

    public function dataProviderForGetUserTasksTesting(): array
    {
        $user = new User();
        $anotherUser = new User();
        $firstTask = new Task();
        $firstTask->setCreatedDate(new DateTime('2021-05-08'));
        $firstTask->setUser($user);
        $secondTask = new Task();
        $secondTask->setCreatedDate(new DateTime('2021-05-15'));
        $secondTask->setUser($anotherUser);

        return [
            [
                $user,
                new ArrayCollection([$firstTask]),
            ],
            [
                $anotherUser,
                new ArrayCollection([$secondTask]),
            ],
        ];
    }

    /**
     * @covers \App\Service\TaskService::getUserTasksByDateRange
     * @dataProvider dataProviderForGetUserTasksByDateRange
     * @param User $user
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param Collection $tasks
     */
    public function testGetUserTasksByDateRange(
        User $user,
        DateTime $startDate,
        DateTime $endDate,
        Collection $tasks
    ): void {
        $this->taskRepositoryMock->expects(static::once())
            ->method('findUserTasksFilteredByDateRange')
            ->with(...[$user, $startDate, $endDate])
            ->willReturn($tasks);

        $this->getTaskService()->getUserTasksByDateRange($user, $startDate, $endDate);
    }

    /**
     * @return array[]
     */
    public function dataProviderForGetUserTasksByDateRange(): array
    {
        $user = new User();
        $firstTask = new Task();
        $firstTask->setCreatedDate(new DateTime('2021-05-08'));
        $firstTask->setUser($user);
        $secondTask = new Task();
        $secondTask->setCreatedDate(new DateTime('2021-05-15'));
        $secondTask->setUser($user);

        return [
            [
                $user,
                new DateTime('2020-01-01'),
                new DateTime('2021-05-20'),
                new ArrayCollection([$firstTask, $secondTask]),
            ],
            [
                $user,
                new DateTime('2020-01-01'),
                new DateTime('2021-05-13'),
                new ArrayCollection([$secondTask]),
            ],
            [
                $user,
                new DateTime('2020-01-01'),
                new DateTime('2020-05-20'),
                new ArrayCollection([$firstTask]),
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->taskRepositoryMock = $this->createMock(TaskRepository::class);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        unset($this->taskRepositoryMock);

        parent::tearDown();
    }

    /**
     * @return TaskService
     */
    private function getTaskService(): TaskService
    {
        return new TaskService($this->taskRepositoryMock);
    }
}
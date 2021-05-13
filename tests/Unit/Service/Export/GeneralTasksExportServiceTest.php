<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Export;

use App\DTO\TasksExportDTO;
use App\Entity\Task;
use App\Tests\Traits\ReflectionTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class GeneralTasksExportServiceTest extends TestCase
{
    use ReflectionTrait;

    /** @var string */
    protected const TYPE_PDF = 'pdf';
    /** @var string */
    protected const TYPE_CSV = 'csv';
    /** @var string */
    protected const TYPE_XLSX = 'xlsx';

    /** @var TranslatorInterface|MockObject */
    protected $translatorMock;

    /**
     * @param string $extension
     * @return TasksExportDTO
     * @throws ReflectionException
     */
    protected function getTestingData(string $extension): TasksExportDTO
    {
        $previousTask = new Task();
        $this->setNonPublicPropertyValue($previousTask, 'id', 5);
        $previousTask->setTitle('Previous title');
        $previousTask->setComment('Previous comment');
        $previousTask->setTimeSpent(15);
        $previousTask->setCreatedDate(new DateTime('yesterday'));

        $currentTask = new Task();
        $this->setNonPublicPropertyValue($currentTask, 'id', 6);
        $currentTask->setTitle('Current title');
        $currentTask->setComment('Current comment');
        $currentTask->setTimeSpent(20);
        $currentTask->setCreatedDate(new DateTime());

        $exportDTO = new TasksExportDTO();
        $exportDTO->setType($extension);
        $exportDTO->setStartDate(new DateTime('2 days ago'));
        $exportDTO->setEndDate(new DateTime());
        $exportDTO->setTasks(new ArrayCollection([$previousTask, $currentTask]));

        return $exportDTO;
    }

    protected function performExpectations(): void
    {
        $this->translatorMock->expects(static::once())
            ->method('trans')
            ->with(...['Total spent'])
            ->willReturn('Total spent:');
    }

    abstract protected function getTestingService();

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->translatorMock = $this->createMock(TranslatorInterface::class);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        unset($this->translatorMock);

        parent::tearDown();
    }
}
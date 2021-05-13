<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Export;

use App\DTO\TasksExportDTO;
use App\Entity\Task;
use App\Exception\UnsupportedExportFormatException;
use App\Service\Export\CsvTasksExportService;
use App\Tests\Traits\ReflectionTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Contracts\Translation\TranslatorInterface;

class CsvTasksExportServiceTest extends TestCase
{
    use ReflectionTrait;

    /** @var string */
    private const TYPE_PDF = 'pdf';
    /** @var string */
    private const TYPE_CSV = 'csv';
    /** @var string */
    private const TYPE_XLSX = 'xlsx';

    /** @var TranslatorInterface|MockObject */
    private $translatorMock;

    /**
     * @covers       \App\Service\Export\CsvTasksExportService::supports
     * @dataProvider dataProviderForSupportsTesting
     * @param TasksExportDTO $exportDTO
     * @param bool $expected
     */
    public function testSupports(TasksExportDTO $exportDTO, bool $expected): void
    {
        $exportService = $this->getCsvTasksExportService();

        static::assertSame($expected, $exportService->supports($exportDTO));
    }

    /**
     * @return array[]
     */
    public function dataProviderForSupportsTesting(): array
    {
        $pdfExport = new TasksExportDTO();
        $pdfExport->setType(self::TYPE_PDF);
        $csvExport = new TasksExportDTO();
        $csvExport->setType(self::TYPE_CSV);
        $xlsxExport = new TasksExportDTO();
        $xlsxExport->setType(self::TYPE_XLSX);

        return [
            self::TYPE_PDF => [$pdfExport, false],
            self::TYPE_CSV => [$csvExport, true],
            self::TYPE_XLSX => [$xlsxExport, false],
        ];
    }

    /**
     * @covers \App\Service\Export\CsvTasksExportService::export
     * @throws ReflectionException
     * @throws UnsupportedExportFormatException
     */
    public function testExport(): void
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
        $exportDTO->setType(self::TYPE_CSV);
        $exportDTO->setStartDate(new DateTime('2 days ago'));
        $exportDTO->setEndDate(new DateTime());
        $exportDTO->setTasks(new ArrayCollection([$previousTask, $currentTask]));

        $this->translatorMock->expects(static::once())
            ->method('trans')
            ->with(...['Total spent'])
            ->willReturn('Total spent:');

        $exportResponseDTO = $this->getCsvTasksExportService()->export($exportDTO);

        static::assertSame('tasks_export.' . self::TYPE_CSV, $exportResponseDTO->getFilename());
        static::assertStringContainsString($exportResponseDTO->getFilename(), $exportResponseDTO->getTempFile());
        static::assertStringContainsString('tmp', $exportResponseDTO->getTempFile());
    }

    /**
     * @return CsvTasksExportService
     */
    private function getCsvTasksExportService(): CsvTasksExportService
    {
        $service = new CsvTasksExportService();
        $service->setTranslator($this->translatorMock);

        return $service;
    }

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
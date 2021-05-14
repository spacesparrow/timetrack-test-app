<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Export;

use App\DTO\TasksExportDTO;
use App\Entity\Task;
use App\Exception\UnsupportedExportFormatException;
use App\Service\Export\ExcelTasksExportService;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use ReflectionException;

class ExcelTasksExportServiceTest extends GeneralTasksExportServiceTest
{
    /** @var string[]  */
    private const HEADERS = [
        'ID',
        'Date',
        'Title',
        'Comment',
        'Time spent'
    ];

    /**
     * @covers       \App\Service\Export\ExcelTasksExportService::supports
     * @dataProvider dataProviderForSupportsTesting
     * @param TasksExportDTO $exportDTO
     * @param bool $expected
     */
    public function testSupports(TasksExportDTO $exportDTO, bool $expected): void
    {
        $exportService = $this->getTestingService();

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
            self::TYPE_CSV => [$csvExport, false],
            self::TYPE_XLSX => [$xlsxExport, true],
        ];
    }

    /**
     * @covers \App\Service\Export\ExcelTasksExportService::export
     * @throws ReflectionException
     * @throws UnsupportedExportFormatException
     * @throws Exception
     */
    public function testExport(): void
    {
        $exportDTO = $this->getTestingData(self::TYPE_XLSX);

        $this->performExpectations();

        $exportResponseDTO = $this->getTestingService()->export($exportDTO);

        static::assertSame('tasks_export.' . self::TYPE_XLSX, $exportResponseDTO->getFilename());
        static::assertStringContainsString($exportResponseDTO->getFilename(), $exportResponseDTO->getTempFile());
        static::assertStringContainsString('tmp', $exportResponseDTO->getTempFile());
    }

    /**
     * @covers \App\Service\Export\CsvTasksExportService::createFilledTemplate
     * @throws ReflectionException
     */
    public function testCreateFilledTemplate(): void
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

        $exportService = $this->getTestingService();

        $spreadsheet = $exportService->createFilledTemplate($exportDTO);
        $activeSheetArray = $spreadsheet->getActiveSheet()->toArray();

        static::assertSame(self::HEADERS, $activeSheetArray[0]);
        static::assertSame($previousTask->toExportArray(), $activeSheetArray[1]);
        static::assertSame($currentTask->toExportArray(), $activeSheetArray[2]);
        static::assertSame(
            [
                'Total spent:',
                null,
                null,
                null,
                35
            ],
            $activeSheetArray[3]
        );
    }

    /**
     * @return ExcelTasksExportService
     */
    protected function getTestingService(): ExcelTasksExportService
    {
        $service = new ExcelTasksExportService();
        $service->setTranslator($this->translatorMock);

        return $service;
    }
}
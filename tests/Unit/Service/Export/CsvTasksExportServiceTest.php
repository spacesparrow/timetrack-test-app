<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Export;

use App\DTO\TasksExportDTO;
use App\DTO\TasksExportResponseDTO;
use App\Entity\Task;
use App\Exception\UnsupportedExportFormatException;
use App\Service\Export\CsvTasksExportService;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use ReflectionException;

class CsvTasksExportServiceTest extends GeneralTasksExportServiceTest
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
     * @covers       \App\Service\Export\CsvTasksExportService::supports
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
        $exportDTO = $this->getTestingData(self::TYPE_CSV);

        $this->performExpectations();

        $exportResponseDTO = $this->getTestingService()->export($exportDTO);

        static::assertSame('tasks_export.' . self::TYPE_CSV, $exportResponseDTO->getFilename());
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
     * @dataProvider dataProviderForCreateTempFileTesting
     * @param string $filename
     * @throws ReflectionException
     */
    public function testCreateTempFile(string $filename): void
    {
        $exportService = $this->getTestingService();

        $tempFile = $this->callNonPublicMethod($exportService, 'createTempFile', [$filename]);

        static::assertStringContainsString($filename, $tempFile);
        static::assertStringContainsString('tmp', $tempFile);
    }

    /**
     * @return string[][]
     */
    public function dataProviderForCreateTempFileTesting(): array
    {
        return [
            'csv' => ['someFile.csv'],
            'pdf' => ['someFile.pdf'],
            'xlsx' => ['someFile.xlsx'],
            'docx' => ['someFile.docx']
        ];
    }

    /**
     * @dataProvider dataProviderForCreateFilenameSuccessTesting
     * @param string $extension
     * @throws ReflectionException
     */
    public function testCreateFilenameSuccess(string $extension): void
    {
        $exportService = $this->getTestingService();

        $filename = $this->callNonPublicMethod($exportService, 'createFilename', [$extension]);

        static::assertSame("tasks_export.$extension", $filename);
    }

    /**
     * @return string[][]
     */
    public function dataProviderForCreateFilenameSuccessTesting(): array
    {
        return [
            self::TYPE_CSV => [self::TYPE_CSV],
            self::TYPE_PDF => [self::TYPE_PDF],
            self::TYPE_XLSX => [self::TYPE_XLSX],
        ];
    }

    /**
     * @dataProvider dataProviderForCreateFilenameFailedTesting
     * @param string $extension
     * @throws ReflectionException
     */
    public function testCreateFilenameFailed(string $extension): void
    {
        $exportService = $this->getTestingService();

        $this->expectException(UnsupportedExportFormatException::class);
        $this->expectExceptionMessage(sprintf('Provided type %s not allowed for import', $extension));

        $this->callNonPublicMethod($exportService, 'createFilename', [$extension]);
    }

    /**
     * @dataProvider dataProviderForGenerateResponseDTOTesting
     * @param string $filename
     * @param string $tempFile
     * @throws ReflectionException
     */
    public function testGenerateResponseDTO(string $filename, string $tempFile): void
    {
        $exportService = $this->getTestingService();

        $responseDTO = $this->callNonPublicMethod($exportService, 'generateResponseDTO', [$filename, $tempFile]);

        static::assertInstanceOf(TasksExportResponseDTO::class, $responseDTO);
        static::assertSame($filename, $responseDTO->getFilename());
        static::assertSame($tempFile, $responseDTO->getTempFile());
    }

    /**
     * @return string[][]
     */
    public function dataProviderForGenerateResponseDTOTesting(): array
    {
        return [
            self::TYPE_PDF => ['tasks_export.pdf', '/tmp/tasks_export.pdf'],
            self::TYPE_CSV => ['tasks_export.csv', '/tmp/tasks_export.csv'],
            self::TYPE_XLSX => ['tasks_export.xlsx', '/tmp/tasks_export.xlsx'],
        ];
    }

    /**
     * @return string[][]
     */
    public function dataProviderForCreateFilenameFailedTesting(): array
    {
        return [
            'docx' => ['docx'],
            'json' => ['json'],
            'yaml' => ['yaml'],
            'html' => ['html'],
            'odt' => ['odt'],
        ];
    }

    /**
     * @return CsvTasksExportService
     */
    protected function getTestingService(): CsvTasksExportService
    {
        $service = new CsvTasksExportService();
        $service->setTranslator($this->translatorMock);

        return $service;
    }
}
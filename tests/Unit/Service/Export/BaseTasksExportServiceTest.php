<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Export;

use App\DTO\TasksExportDTO;
use App\DTO\TasksExportResponseDTO;
use App\Entity\Task;
use App\Exception\UnsupportedExportFormatException;
use App\Service\Export\BaseTasksExportService;
use App\Tests\Traits\ReflectionTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Contracts\Translation\TranslatorInterface;

class BaseTasksExportServiceTest extends TestCase
{
    use ReflectionTrait;

    /** @var string */
    private const TYPE_PDF = 'pdf';
    /** @var string */
    private const TYPE_CSV = 'csv';
    /** @var string */
    private const TYPE_XLSX = 'xlsx';

    /** @var string[]  */
    private const HEADERS = [
        'ID',
        'Date',
        'Title',
        'Comment',
        'Time spent'
    ];

    /** @var TranslatorInterface|MockObject */
    private $translatorMock;

    /**
     * @covers \App\Service\Export\BaseTasksExportService::createFilledSpreadsheet
     * @throws ReflectionException
     */
    public function testCreateFilledSpreadsheet(): void
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

        $exportService = $this->getBaseTasksExportService();

        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = $this->callNonPublicMethod($exportService, 'createFilledSpreadsheet', [$exportDTO]);
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
     * @covers       \App\Service\Export\BaseTasksExportService::createTempFile
     * @dataProvider dataProviderForCreateTempFileTesting
     * @param string $filename
     * @throws ReflectionException
     */
    public function testCreateTempFile(string $filename): void
    {
        $exportService = $this->getBaseTasksExportService();

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
     * @covers       \App\Service\Export\BaseTasksExportService::createFilename
     * @dataProvider dataProviderForCreateFilenameSuccessTesting
     * @param string $extension
     * @throws ReflectionException
     */
    public function testCreateFilenameSuccess(string $extension): void
    {
        $exportService = $this->getBaseTasksExportService();

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
     * @covers       \App\Service\Export\BaseTasksExportService::createFilename
     * @dataProvider dataProviderForCreateFilenameFailedTesting
     * @param string $extension
     * @throws ReflectionException
     */
    public function testCreateFilenameFailed(string $extension): void
    {
        $exportService = $this->getBaseTasksExportService();

        $this->expectException(UnsupportedExportFormatException::class);
        $this->expectExceptionMessage(sprintf('Provided type %s not allowed for import', $extension));

        $this->callNonPublicMethod($exportService, 'createFilename', [$extension]);
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
     * @covers       \App\Service\Export\BaseTasksExportService::generateResponseDTO
     * @dataProvider dataProviderForGenerateResponseDTOTesting
     * @param string $filename
     * @param string $tempFile
     * @throws ReflectionException
     */
    public function testGenerateResponseDTO(string $filename, string $tempFile): void
    {
        $exportService = $this->getBaseTasksExportService();

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
     * @return BaseTasksExportService
     */
    private function getBaseTasksExportService(): BaseTasksExportService
    {
        $exportService = new BaseTasksExportService();
        $exportService->setTranslator($this->translatorMock);

        return $exportService;
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
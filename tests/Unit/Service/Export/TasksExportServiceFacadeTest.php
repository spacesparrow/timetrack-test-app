<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Export;

use App\DTO\TasksExportDTO;
use App\Exception\UnsupportedExportFormatException;
use App\Service\Export\CsvTasksExportService;
use App\Service\Export\ExcelTasksExportService;
use App\Service\Export\PdfTasksExportService;
use App\Service\Export\TasksExportServiceFacade;
use App\Tests\Traits\ReflectionTrait;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class TasksExportServiceFacadeTest extends GeneralTasksExportServiceTest
{
    use ReflectionTrait;

    /** @var PdfTasksExportService|MockObject */
    private $pdfExportMock;
    /** @var ExcelTasksExportService|MockObject */
    private $xlsxExportMock;
    /** @var CsvTasksExportService|MockObject */
    private $csvExportMock;

    /**
     * @covers \App\Service\Export\TasksExportServiceFacade::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        $facade = $this->getTestingService();
        $services = $this->getNonPublicPropertyValue($facade, 'exportServices');

        static::assertIsArray($services);
        static::assertContains(
            $this->pdfExportMock,
            $services
        );
        static::assertContains(
            $this->xlsxExportMock,
            $services
        );
        static::assertContains(
            $this->csvExportMock,
            $services
        );
    }

    /**
     * @covers       \App\Service\Export\TasksExportServiceFacade::export
     * @dataProvider dataProviderForExportTesting
     * @param TasksExportDTO $exportDTO
     * @param string|null $exception
     * @throws UnsupportedExportFormatException
     */
    public function testExport(TasksExportDTO $exportDTO, ?string $exception = null): void
    {
        if (!$exception) {
            $responsibleClass = "{$exportDTO->getType()}ExportMock";
            $this->{$responsibleClass}->expects(static::once())
                ->method('export')
                ->with(...[$exportDTO]);
        } else {
            $this->expectException($exception);
        }

        $this->getTestingService()->export($exportDTO);
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function dataProviderForExportTesting(): array
    {
        $pdfExportDTO = $this->getTestingData(self::TYPE_PDF);
        $xlsxExportDTO = $this->getTestingData(self::TYPE_XLSX);
        $csvExportDTO = $this->getTestingData(self::TYPE_CSV);
        $docxExportDTO = $this->getTestingData('docx');
        $odtExportDTO = $this->getTestingData('odt');

        return [
            self::TYPE_PDF => [$pdfExportDTO],
            self::TYPE_XLSX => [$xlsxExportDTO],
            self::TYPE_CSV => [$csvExportDTO],
            'docx' => [$docxExportDTO, UnsupportedExportFormatException::class],
            'odt' => [$odtExportDTO, UnsupportedExportFormatException::class],
        ];
    }

    /**
     * @return TasksExportServiceFacade
     */
    protected function getTestingService(): TasksExportServiceFacade
    {
        return new TasksExportServiceFacade(
            [
                $this->pdfExportMock,
                $this->xlsxExportMock,
                $this->csvExportMock
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->pdfExportMock = $this->createPartialMock(PdfTasksExportService::class, ['export']);
        $this->xlsxExportMock = $this->createPartialMock(ExcelTasksExportService::class, ['export']);
        $this->csvExportMock = $this->createPartialMock(CsvTasksExportService::class, ['export']);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        unset(
            $this->pdfExportMock,
            $this->xlsxExportMock,
            $this->csvExportMock
        );

        parent::tearDown();
    }
}